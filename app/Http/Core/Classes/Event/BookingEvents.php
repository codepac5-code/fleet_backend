<?php

namespace App\Http\Core\Classes\Event;

use App\Http\Core\GeoServices\ShardManager;

use App\Http\Core\Classes\Dispatch\DriverLocationStore;
use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Models\RideBooking;
use Throwable;

class BookingEvents
{
    public static function statusChanged(RideBooking $booking, ?string $source = null, ?string $reason = null): DomainEvent
    {
        $payload = [
            'booking_id' => (int) $booking->id,
            'status' => (string) $booking->status,
            'office_id' => (int) $booking->office_id,
            'source' => $source ?? ($booking->source ? (string) $booking->source : BookingSource::SYSTEM),
            'at' => now()->toIso8601ZuluString(),
        ];

        if ($reason !== null && $reason !== '') {
            $payload['reason'] = $reason;
        }

        // The rider app closes the trip on this event and reads `final_fare`
        // for the amount it shows; without it the UI falls back to whatever the
        // last meter tick said, which is not the settled total. Minor units, to
        // match `booking.meter.running_fare`.
        if ($booking->status === BookingStatus::COMPLETED) {
            $payload['final_fare'] = (int) ($booking->total_minor ?? 0);
            $payload['currency'] = (string) $booking->currency_code;
        }

        // "Driver is on the way" — the rider app reads `eta_minutes` here to
        // headline the wait. Derived from the driver's live position rather than
        // a constant, so the number means something.
        if ($booking->status === BookingStatus::ARRIVING) {
            $eta = self::etaMinutesToPickup($booking);

            if ($eta !== null) {
                $payload['eta_minutes'] = $eta;
            }
        }

        return new DomainEvent(
            EventType::BOOKING_STATUS_CHANGED,
            [Channel::booking((int) $booking->id), Channel::user((int) $booking->user_id)],
            $payload
        );
    }

    /**
     * A brand-new order landed and needs handling — surfaced LIVE to the office
     * that owns it and to the fleet admin room. Office/admin channels only; the
     * rider/driver apps never subscribe to these, so this is purely additive to
     * the panel and cannot reach the apps.
     */
    public static function orderCreated(RideBooking $booking): DomainEvent
    {
        $channels = [Channel::admin()];

        if ((int) $booking->office_id > 0) {
            $channels[] = Channel::office((int) $booking->office_id);
        }

        return new DomainEvent(
            EventType::ORDER_CREATED,
            $channels,
            [
                'booking_id' => (int) $booking->id,
                'office_id' => (int) $booking->office_id,
                'user_id' => (int) $booking->user_id,
                'service' => (string) $booking->service,
                'service_class' => (string) $booking->service_class,
                'status' => (string) $booking->status,
                'pickup_title' => $booking->pickup_title,
                'dropoff_title' => $booking->dropoff_title,
                'total_minor' => (int) ($booking->total_minor ?? 0),
                'currency' => (string) $booking->currency_code,
                'at' => now()->toIso8601ZuluString(),
            ]
        );
    }

    /**
     * Minutes for the assigned driver to reach the pickup, from their live
     * position in Redis. Null when we simply do not know (no driver, no fix, no
     * pickup) — better to omit the key and let the app keep its previous ETA
     * than to publish a made-up number. Uses the same ~400 m/min city pace as
     * the dispatcher so both sides agree.
     */
    private static function etaMinutesToPickup(RideBooking $booking): ?int
    {
        try {
            $driverId = (int) ($booking->driver_id ?? 0);

            if ($driverId <= 0 || $booking->pickup_lat === null || $booking->pickup_lng === null) {
                return null;
            }

            $region = ShardManager::shardKey();
            $position = DriverLocationStore::get($region, $driverId);

            if ($position === null) {
                return null;
            }

            $metres = Geo::haversineMeters(
                (float) $position['lat'],
                (float) $position['lng'],
                (float) $booking->pickup_lat,
                (float) $booking->pickup_lng
            );

            return max(1, (int) ceil($metres / 400));
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function meter(RideBooking $booking, int $elapsedSeconds, int $distanceMeters, int $runningFareMinor): DomainEvent
    {
        return new DomainEvent(
            EventType::BOOKING_METER,
            [Channel::booking((int) $booking->id), Channel::user((int) $booking->user_id)],
            [
                'booking_id' => (int) $booking->id,
                'elapsed_s' => $elapsedSeconds,
                'distance_m' => $distanceMeters,
                'running_fare' => $runningFareMinor,
                'currency' => (string) $booking->currency_code,
            ]
        );
    }
}
