<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Auth\PhoneNumber;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Ledger\DriverStatementRepository;
use App\Http\Services\User\Support\Presenters\BookingPresenter;
use App\Http\Services\User\Support\Reply;
use App\Models\RideBooking;
use App\Models\RideRating;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver-side ride reads: details, rider masked-contact, history, cancel-impact.
 * All scoped to bookings assigned to the authenticated driver.
 */
class DriverTripQueryController extends Controller
{
    public function __construct(private DriverStatementRepository $statement)
    {
    }

    private function ownedBooking(Request $request, int $id): RideBooking
    {
        $booking = RideBooking::query()->where('id', $id)->where('driver_id', $request->user()->id)->first();

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $booking;
    }

    /**
     * A booking the driver may READ: one already assigned to them, or one they
     * are currently being offered.
     *
     * A dispatch offer only sets `ride_bookings.driver_id` on accept, so at
     * offer time an ownership-only check 404s — leaving the driver's offer card
     * with no fare, payment method, route or stops to decide on. A live
     * (offered, unexpired) offer grants read access to exactly that booking and
     * nothing more; write actions still require real ownership.
     *
     * The status compared here MUST be `OfferStatus::OFFERED`. It used to be the
     * literal `'pending'` — a value this system never writes and which is not in
     * the enum at all — so the subquery never matched, `GET driver/trips/{id}`
     * 404'd for every real offer, and the driver's ride-request card never
     * opened. Caught only by running the cycle on a device; the unit test had
     * seeded `'pending'` too, so it agreed with the bug and passed.
     */
    private function readableBooking(Request $request, int $id): RideBooking
    {
        $driverId = (int) $request->user()->id;

        $booking = RideBooking::query()
            ->where('id', $id)
            ->where(fn ($q) => $q
                ->where('driver_id', $driverId)
                ->orWhereExists(fn ($e) => $e
                    ->selectRaw('1')
                    ->from('dispatch_offers')
                    ->whereColumn('dispatch_offers.booking_id', 'ride_bookings.id')
                    ->where('dispatch_offers.driver_id', $driverId)
                    ->where('dispatch_offers.status', OfferStatus::OFFERED)
                    ->where('dispatch_offers.expires_at', '>', now())))
            ->first();

        if ($booking === null) {
            throw DomainException::notFound();
        }

        return $booking;
    }

    public function rateRider(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'tags' => ['nullable', 'array'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'book_again' => ['nullable', 'boolean'],
            'favorite' => ['nullable', 'boolean'],
        ]);

        $booking = $this->ownedBooking($request, $id);

        $rating = RideRating::query()->updateOrCreate(
            ['booking_id' => $id, 'rater_type' => 'driver', 'rater_id' => (int) $request->user()->id],
            [
                'ratee_type' => 'rider',
                'ratee_id' => (int) $booking->user_id,
                'stars' => (int) $data['stars'],
                'tags' => $data['tags'] ?? [],
                'comment' => $data['comment'] ?? null,
                'book_again' => array_key_exists('book_again', $data) ? (bool) $data['book_again'] : null,
                'favorite' => ! empty($data['favorite']),
            ],
        );

        // Stamp the booking so the app can tell it's already rated.
        if ($booking->rated_at === null) {
            $booking->rated_at = now();
            $booking->save();
        }

        return Reply::ok(BookingPresenter::rating($rating), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        // Readable while merely OFFERED — this is what fills the driver's offer
        // card (fare, payment method, route, stops) before they accept.
        $booking = $this->readableBooking($request, $id);
        $rider = User::query()->find($booking->user_id);
        $rating = RideRating::query()->where('booking_id', $id)->first();

        return Reply::ok([
            'booking' => BookingPresenter::row($booking),
            'rider' => [
                'firstName' => (string) ($rider->firstName ?? ''),
                'lastName' => (string) ($rider->lastName ?? ''),
            ],
            'rating' => $rating !== null ? BookingPresenter::rating($rating) : null,
        ]);
    }

    public function riderContact(Request $request, int $id): JsonResponse
    {
        $booking = $this->ownedBooking($request, $id);
        $rider = User::query()->find($booking->user_id);
        // Rider phoneNumber is already full E.164; only prepend the dialCode when
        // a bare national number is stored.
        $phone = (string) ($rider->phoneNumber ?? '');
        $e164 = str_starts_with($phone, '+')
            ? $phone
            : '+' . ltrim((string) ($rider->dialCode ?? ''), '+') . $phone;

        // No proxy/masking gateway configured → the driver calls the rider
        // directly. `masked_phone` is for display; `proxy_number` carries the
        // dialable E.164 so the app's Call button actually works. Only active
        // trips expose it (a completed/cancelled trip returns no number).
        $dialable = ($rider !== null && $e164 !== '+' && in_array($booking->status, ['assigned', 'arrived', 'in_progress'], true))
            ? $e164
            : null;

        return Reply::ok([
            'masked_phone' => $rider !== null ? PhoneNumber::mask($e164) : null,
            'call_via' => 'direct',
            'proxy_number' => $dialable,
            'expires_at' => null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $status = (string) $request->query('status', 'all');

        $query = RideBooking::query()->where('driver_id', $request->user()->id);
        if ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $items = $query->latest('id')->limit(50)->get()
            ->map(fn (RideBooking $b) => BookingPresenter::row($b))->all();

        return Reply::ok(['items' => $items, 'nextCursor' => null]);
    }

    public function cancelImpact(Request $request, int $id): JsonResponse
    {
        $this->ownedBooking($request, $id);

        // Real current acceptance rate. There is no cancellation-penalty engine
        // yet, so "after" equals "before" (no score impact) — but the figure the
        // driver sees is now their actual rate, not a hardcoded 100.
        $o = $this->statement->offerCounts((int) $request->user()->id);
        $decided = (int) $o['accepted'] + (int) $o['rejected'] + (int) $o['expired'];
        $acceptance = $decided > 0 ? (int) round((int) $o['accepted'] / $decided * 100) : 100;

        $reason = strtolower((string) $request->query('reason', ''));
        $protected = $reason === '' || str_contains($reason, 'vehicle') || str_contains($reason, 'emergency');

        return Reply::ok([
            'acceptanceBefore' => $acceptance,
            'acceptanceAfter' => $acceptance,
            'weeklyRemaining' => '3 of 3',
            'protected' => $protected,
            'feeQar' => 0,
        ]);
    }
}
