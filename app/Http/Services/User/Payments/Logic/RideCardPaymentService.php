<?php

namespace App\Http\Services\User\Payments\Logic;

use App\Http\Core\Classes\Account\CardGateway;
use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Payment\PaymentService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Const\Payment\PaymentKind;
use App\Http\Core\Const\Payment\PaymentStatus;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Models\CommissionSnapshot;
use App\Models\LedgerPayment;
use App\Models\RideBooking;

/**
 * Card / Apple Pay for a ride, PRE-AUTHORISED at booking (Uber-style).
 *
 * When the rider picks `card`, the ESTIMATED fare is authorised (held, not
 * charged) on the card via a manual-capture PaymentIntent the rider confirms in
 * the app. At completion the FINAL fare — always ≤ the estimate, because the
 * meter is capped at the quoted route — is captured and settled three-ways
 * through the ledger (kind `ride` → distributeDigital); the held excess is
 * released. A cancelled ride voids the hold. If the capture ever fails (declined
 * / expired hold) the trip stays `payment_due`, and {@see intent}/{@see verify}
 * are the rider's fallback to pay the final fare directly.
 */
class RideCardPaymentService
{
    public function __construct(
        private CardGateway $cards,
        private PaymentService $payments,
        private TariffResolver $tariffs,
        private PricingService $pricing
    ) {
    }

    /**
     * Authorise (hold) the estimated fare on the rider's card before the booking
     * is created. Returns the PaymentIntent the app confirms; its id is then
     * passed to `POST bookings` as `card_authorization_id`.
     */
    public function authorize(int $userId, array $in, string $idempotencyKey): array
    {
        $officeId = (int) ($in['office_id'] ?? 0);
        $service = (string) ($in['service'] ?? 'ride');
        $serviceClass = (string) ($in['service_class'] ?? '');
        $subServiceId = (int) ($in['sub_service_id'] ?? 0);

        $tariff = $this->tariffs->forOfficeServiceOrSub($officeId, $subServiceId ?: null, $service, $serviceClass);

        if ($tariff === null) {
            throw DomainException::notFound('tariff_not_found');
        }

        $distance = (int) ($in['distance_m'] ?? 0);
        if ($distance <= 0) {
            $distance = (int) round(Geo::haversineMeters(
                (float) $in['pickup']['lat'], (float) $in['pickup']['lng'],
                (float) $in['dropoff']['lat'], (float) $in['dropoff']['lng']
            ));
        }
        $duration = (int) ($in['duration_s'] ?? 0);
        if ($duration <= 0) {
            $duration = (int) round($distance / 8);
        }

        $currency = (string) $tariff['currency_code'];
        // Authorise the (pre-discount) fare — the final capture is the booking's
        // total, which is never larger, so the hold always covers it.
        $amountMinor = (int) $this->pricing->quote($tariff, $distance, $duration)['fare_minor'];

        if ($amountMinor <= 0) {
            throw DomainException::make('invalid_fare', 422);
        }

        $key = 'ridecard:' . ($idempotencyKey !== '' ? $idempotencyKey : ($userId . ':' . $officeId . ':' . $amountMinor));

        $pi = $this->cards->paymentIntent($userId, $amountMinor, $currency, null, $key, [
            'purpose' => 'ride_authorization',
        ], true);

        $this->payments->createRideIntent($userId, null, $amountMinor, $currency, 'stripe', $key, $pi['id']);

        return [
            'paymentIntentId' => $pi['id'],
            'clientSecret' => $pi['clientSecret'],
            'status' => $pi['status'],
            'requiresAction' => $pi['requiresAction'],
            'amountMinor' => $amountMinor,
            'currency' => $currency,
            'publishableKey' => (string) config('services.stripe.public'),
        ];
    }

    /**
     * Bind a confirmed authorization to a freshly created card booking. Verifies
     * the hold belongs to the rider, is genuinely authorised (`requires_capture`)
     * and covers the fare, then records the intent on the booking. Throws inside
     * the booking transaction so an invalid/absent hold rolls the booking back.
     */
    public function attachAuthorization(int $userId, RideBooking $booking, string $paymentIntentId): void
    {
        if ($paymentIntentId === '') {
            throw DomainException::make('card_authorization_required', 422, 'Card authorization is required for this ride.');
        }

        $payment = LedgerPayment::query()
            ->where('provider_ref', $paymentIntentId)
            ->where('owner_id', $userId)
            ->where('kind', PaymentKind::RIDE)
            ->where('status', PaymentStatus::PENDING)
            ->first();

        if ($payment === null || (int) $payment->amount_minor < (int) $booking->total_minor) {
            throw DomainException::make('card_authorization_invalid', 422, 'The card authorization does not cover this ride.');
        }

        if ($this->cards->paymentIntentStatus($paymentIntentId) !== 'requires_capture') {
            throw DomainException::make('card_not_authorized', 422, 'The card was not authorized. Please try again.');
        }

        $payment->booking_id = (int) $booking->id;
        $payment->save();

        $booking->stripe_payment_intent_id = $paymentIntentId;
    }

    /**
     * Capture the final fare from the pre-authorised hold at completion, then
     * settle the ride. Best-effort: a failed capture leaves the trip unsettled
     * (`payment_due`) so the rider can pay via the {@see intent} fallback.
     */
    public function captureForBooking(RideBooking $booking): void
    {
        $pi = (string) ($booking->stripe_payment_intent_id ?? '');

        if ($pi === '') {
            return;
        }

        $payment = LedgerPayment::query()
            ->where('provider_ref', $pi)
            ->where('kind', PaymentKind::RIDE)
            ->first();

        if ($payment === null || $payment->status !== PaymentStatus::PENDING) {
            return;
        }

        $capture = (int) $booking->total_minor;

        if ($capture <= 0) {
            $this->cards->cancelPaymentIntent($pi);

            return;
        }

        if ($this->cards->capturePaymentIntent($pi, $capture) !== 'succeeded') {
            return;
        }

        // Persist the reconciled fare BEFORE settling: settlement re-reads the
        // booking from the DB to build the three-way split, so the final
        // total_minor must already be stored, not just held in memory.
        $booking->save();

        // Record what was actually captured and bind the booking (settlement
        // resolves the ride from payment.booking_id), then settle through the
        // same idempotent path the webhook uses.
        $payment->amount_minor = $capture;
        $payment->booking_id = (int) $booking->id;
        $payment->save();

        $this->payments->handleGatewayEvent($payment->idempotency_key, PaymentStatus::SUCCEEDED, $pi);
    }

    /** Void the hold on a cancelled card ride (no-op once captured). */
    public function release(RideBooking $booking): void
    {
        $pi = (string) ($booking->stripe_payment_intent_id ?? '');

        if ($pi === '') {
            return;
        }

        $payment = LedgerPayment::query()
            ->where('provider_ref', $pi)
            ->where('kind', PaymentKind::RIDE)
            ->first();

        if ($payment !== null && $payment->status === PaymentStatus::SUCCEEDED) {
            return;
        }

        $this->cards->cancelPaymentIntent($pi);

        if ($payment !== null && $payment->status === PaymentStatus::PENDING) {
            $payment->status = PaymentStatus::FAILED;
            $payment->save();
        }
    }

    /**
     * FALLBACK — charge the final fare of a completed card trip directly when the
     * pre-auth capture failed. Mints an immediate-capture PaymentIntent the rider
     * confirms; settlement runs through {@see verify}.
     */
    public function intent(int $userId, int $bookingId): array
    {
        $booking = $this->ownedBooking($userId, $bookingId);

        if ($booking->status !== BookingStatus::COMPLETED) {
            throw DomainException::conflict('trip_not_completed', 'This trip cannot be paid yet.');
        }

        if (strtolower((string) $booking->payment_method) !== 'card') {
            throw DomainException::conflict('not_a_card_trip', 'This trip is not paid by card.');
        }

        $amountMinor = (int) $booking->total_minor;

        if ($amountMinor <= 0) {
            throw DomainException::make('nothing_to_pay', 422, 'There is nothing to pay on this trip.');
        }

        if ($this->isSettled($bookingId)) {
            throw DomainException::conflict('trip_already_paid', 'This trip has already been paid.');
        }

        $currency = (string) $booking->currency_code;
        $key = 'ridepay:' . $bookingId;

        $pi = $this->cards->paymentIntent($userId, $amountMinor, $currency, null, $key, [
            'purpose' => 'ride',
            'booking_id' => (string) $bookingId,
        ]);

        $this->payments->createRideIntent($userId, $bookingId, $amountMinor, $currency, 'stripe', $key, $pi['id']);

        $booking->stripe_payment_intent_id = $pi['id'];
        $booking->save();

        return [
            'paymentIntentId' => $pi['id'],
            'clientSecret' => $pi['clientSecret'],
            'status' => $pi['status'],
            'requiresAction' => $pi['requiresAction'],
            'amountMinor' => $amountMinor,
            'currency' => $currency,
            'publishableKey' => (string) config('services.stripe.public'),
        ];
    }

    public function verify(int $userId, int $bookingId, string $paymentIntentId): array
    {
        $payment = LedgerPayment::query()
            ->where('provider_ref', $paymentIntentId)
            ->where('owner_id', $userId)
            ->where('booking_id', $bookingId)
            ->where('kind', PaymentKind::RIDE)
            ->first();

        if ($payment === null) {
            throw DomainException::make('payment_not_found', 404);
        }

        if ($payment->status === PaymentStatus::PENDING) {
            if ($this->cards->paymentIntentStatus($paymentIntentId) === 'succeeded') {
                $this->payments->handleGatewayEvent($payment->idempotency_key, PaymentStatus::SUCCEEDED, $paymentIntentId);
                $payment->refresh();
            }
        }

        return [
            'status' => $payment->status,
            'paid' => $payment->status === PaymentStatus::SUCCEEDED,
            'bookingId' => $bookingId,
        ];
    }

    private function ownedBooking(int $userId, int $bookingId): RideBooking
    {
        $booking = RideBooking::query()->find($bookingId);

        if ($booking === null || (int) $booking->user_id !== $userId) {
            throw DomainException::notFound();
        }

        return $booking;
    }

    private function isSettled(int $bookingId): bool
    {
        return CommissionSnapshot::query()->where('booking_id', $bookingId)->exists();
    }
}
