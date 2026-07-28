<?php

namespace App\Http\Core\Classes\Event;

use App\Http\Core\Classes\Ride\RideBoardBridge;
use App\Http\Core\Const\Event\EventType;
use App\Models\RideBooking;
use Throwable;

/**
 * Mirrors every app-ride status change onto the legacy order board (Redis) that
 * the panel Live Trip Monitor reads. Hooked into the event pipeline so a single
 * point catches EVERY transition (create → matching → assigned → … → completed /
 * cancelled) from every service. Runs inside the per-shard relay drain, so the
 * active shard — and thus the board's country isolation — is already set.
 */
class OrderBoardPublisher implements EventPublisher
{
    private const KINDS = ['booking', 'user', 'driver', 'office', 'admin'];

    public function publish(string $channel, string $type, array $payload): void
    {
        if ($type !== EventType::BOOKING_STATUS_CHANGED) {
            return;
        }

        // BOOKING_STATUS_CHANGED targets both the booking and user channels; act
        // ONCE per event by keying off the booking channel only.
        if ($this->kind($channel) !== 'booking') {
            return;
        }

        $id = (int) ($payload['booking_id'] ?? 0);

        if ($id <= 0) {
            return;
        }

        try {
            $booking = RideBooking::query()->find($id);

            if ($booking !== null) {
                RideBoardBridge::sync($booking);
            }
        } catch (Throwable $e) {
            // best-effort — the board is a mirror, never the source of truth
        }
    }

    /** The channel kind, after dropping a leading shard/region segment. */
    private function kind(string $channel): string
    {
        $parts = explode('.', $channel);

        if (count($parts) >= 2 && ! in_array($parts[0], self::KINDS, true)) {
            array_shift($parts);
        }

        return $parts[0] ?? '';
    }
}
