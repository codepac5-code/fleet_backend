<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Auth\RiderProvisioningService;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Notification\SmsSender;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\RideBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficeBookingService
{
    public function __construct(
        private RiderProvisioningService $riders,
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private DispatchService $dispatch,
        private FleetWalletService $wallet,
        private RideBookingRepository $repository,
        private ?EventBus $events = null,
        private ?SmsSender $sms = null
    ) {
    }

    public function quote(int $officeId, string $service, string $serviceClass, float $pLat, float $pLng, float $dLat, float $dLng, ?int $distanceM = null, ?int $durationS = null): array
    {
        $tariff = $this->tariffs->forOfficeService($officeId, $service, $serviceClass);

        if ($tariff === null) {
            throw DomainException::make('tariff_not_found', 404);
        }

        $distance = $distanceM ?? Geo::haversineMeters($pLat, $pLng, $dLat, $dLng);
        $duration = $durationS ?? (int) round($distance / 8);
        $quote = $this->pricing->quote($tariff, $distance, $duration);

        return [
            'currency_code' => $tariff['currency_code'],
            'distance_m' => $distance,
            'duration_s' => $duration,
            'suggested_fare_minor' => (int) $quote['fare_minor'],
            'breakdown' => $quote['breakdown'] ?? [],
        ];
    }

    public function create(array $in, string $createdBy): array
    {
        $officeId = (int) $in['office_id'];
        $service = (string) ($in['service'] ?? 'ride');
        $serviceClass = (string) $in['service_class'];

        $tariff = $this->tariffs->forOfficeService($officeId, $service, $serviceClass);

        if ($tariff === null) {
            throw DomainException::make('tariff_not_found', 404);
        }

        [$customer] = $this->riders->findOrCreateByPhone((string) $in['phone'], $in['name'] ?? null);

        $pickup = $in['pickup'];
        $dropoff = $in['dropoff'];
        $distance = (int) ($in['distance_m'] ?? Geo::haversineMeters((float) $pickup['lat'], (float) $pickup['lng'], (float) $dropoff['lat'], (float) $dropoff['lng']));
        $duration = (int) ($in['duration_s'] ?? round($distance / 8));

        $manualFare = $in['fare_minor'] ?? null;
        $fare = ($manualFare !== null && $manualFare !== '')
            ? (int) $manualFare
            : (int) $this->pricing->quote($tariff, $distance, $duration)['fare_minor'];

        if ($fare <= 0) {
            throw DomainException::make('invalid_fare', 422);
        }

        $paymentMethod = ($in['payment_method'] ?? 'cash') === 'office_wallet' ? 'office_wallet' : 'cash';
        $currency = $tariff['currency_code'];
        $mode = $in['assign']['mode'] ?? 'broadcast';
        $assignDriverId = ($mode === 'driver' && !empty($in['assign']['driver_id'])) ? (int) $in['assign']['driver_id'] : null;

        $connection = (new RideBooking)->getConnectionName();

        $booking = DB::connection($connection)->transaction(function () use ($customer, $officeId, $createdBy, $service, $serviceClass, $pickup, $dropoff, $distance, $duration, $currency, $fare, $paymentMethod, $in, $assignDriverId) {
            $booking = $this->repository->create([
                'user_id' => (int) $customer->id,
                'office_id' => $officeId,
                'source' => BookingSource::OFFICE,
                'created_by' => $createdBy,
                'service' => $service,
                'service_class' => $serviceClass,
                'pricing_style' => 'manual',
                'status' => BookingStatus::MATCHING,
                'pickup_lat' => (float) $pickup['lat'],
                'pickup_lng' => (float) $pickup['lng'],
                'pickup_note' => $pickup['note'] ?? null,
                'pickup_title' => $pickup['title'] ?? null,
                'dropoff_lat' => (float) $dropoff['lat'],
                'dropoff_lng' => (float) $dropoff['lng'],
                'dropoff_title' => $dropoff['title'] ?? null,
                'distance_m' => $distance,
                'duration_s' => $duration,
                'currency_code' => $currency,
                'fare_minor' => $fare,
                'discount_minor' => 0,
                'total_minor' => $fare,
                'held_minor' => 0,
                'payment_method' => $paymentMethod,
                'passengers' => $in['passengers'] ?? null,
                'luggage' => $in['luggage'] ?? null,
                'idempotency_key' => $in['idempotency_key'] ?? ('office:' . Str::uuid()->toString()),
            ]);

            if ($paymentMethod === 'office_wallet') {
                $balance = $this->wallet->lockWalletBalanceMinor(OwnerType::OFFICE, $officeId, $currency);

                if ($balance < $fare) {
                    throw DomainException::make('insufficient_office_balance', 422);
                }

                $this->wallet->holdRideFromOffice((int) $booking->id, $officeId, $fare, $currency, 'office-hold:' . $booking->id);
                $booking->held_minor = $fare;
                $this->repository->save($booking);
            }

            $this->dispatch->createJob((int) $booking->id, $officeId, $serviceClass, (float) $pickup['lat'], (float) $pickup['lng']);

            if ($assignDriverId !== null) {
                if (! $this->dispatch->assignDriver((int) $booking->id, $assignDriverId)) {
                    throw DomainException::make('driver_assign_failed', 409);
                }
            } else {
                $this->dispatch->offerWave((int) $booking->id);
            }

            return $booking;
        });

        $this->emitCreated($booking, $customer, $assignDriverId);
        $this->sendInvite($customer);

        return [
            'booking_id' => (int) $booking->id,
            'status' => $assignDriverId !== null ? BookingStatus::ASSIGNED : BookingStatus::MATCHING,
            'customer_id' => (int) $customer->id,
            'assigned_driver_id' => $assignDriverId,
            'total_minor' => $fare,
            'currency_code' => $currency,
            'payment_method' => $paymentMethod,
        ];
    }

    private function sendInvite($customer): void
    {
        if ($this->sms === null) {
            return;
        }

        $link = AppSettings::string('android_app_url', '') ?: AppSettings::string('ios_app_url', '');
        $appName = AppSettings::string('app_name_en', 'Fleet');
        $message = $link !== ''
            ? $appName . ': ' . 'تم حجز رحلتك. حمّل التطبيق وتتبّع رحلتك: ' . $link
            : $appName . ': ' . 'تم حجز رحلتك عبر المكتب.';

        $this->sms->send((string) $customer->phoneNumber, $message);
    }

    private function emitCreated($booking, $customer, ?int $driverId): void
    {
        if ($this->events === null) {
            return;
        }

        $channels = [
            Channel::office((int) $booking->office_id),
            Channel::user((int) $customer->id),
            Channel::booking((int) $booking->id),
        ];

        if ($driverId !== null) {
            $channels[] = Channel::driver($driverId);
        }

        $this->events->emit(new DomainEvent(
            EventType::BOOKING_STATUS_CHANGED,
            $channels,
            [
                'booking_id' => (int) $booking->id,
                'status' => $driverId !== null ? BookingStatus::ASSIGNED : BookingStatus::MATCHING,
                'source' => BookingSource::OFFICE,
                'office_id' => (int) $booking->office_id,
            ]
        ));
    }
}
