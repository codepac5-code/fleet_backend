<?php

namespace App\Http\Core\Classes\Event;

use App\Http\Core\GeoServices\ShardManager;
use App\Models\RideBooking;

class ChannelAuthorizer
{
    private const KINDS = ['user', 'driver', 'office', 'booking', 'admin'];

    public function authorize(string $type, int $id, string $channel): bool
    {
        if ($channel === '') {
            return false;
        }

        $parts = explode('.', $channel);

        // Optional leading shard segment (e.g. `sy.driver.33`, `sy.admin`). It
        // MUST match the shard the caller authenticated against, so nobody can
        // subscribe to another database's room even if the numeric id matches.
        // The shard segment is present exactly when the first part is not itself
        // a room kind — this covers both id-rooms and the id-less admin room.
        if (! in_array($parts[0], self::KINDS, true)) {
            $shard = ShardManager::shardKey();
            if ($shard !== '' && strtolower($parts[0]) !== $shard) {
                return false;
            }
            array_shift($parts);
        }

        // Fleet-wide admin room carries no id.
        if (count($parts) === 1 && $parts[0] === 'admin') {
            return $type === 'admin';
        }

        if (count($parts) !== 2 || ! ctype_digit($parts[1])) {
            return false;
        }

        $kind = $parts[0];
        $targetId = (int) $parts[1];

        return match ($kind) {
            'user' => $type === 'user' && $id === $targetId,
            'driver' => $type === 'driver' && $id === $targetId,
            'office' => $type === 'office' && $id === $targetId,
            'booking' => $this->ownsBooking($type, $id, $targetId),
            default => false,
        };
    }

    private function ownsBooking(string $type, int $id, int $bookingId): bool
    {
        $booking = RideBooking::query()->find($bookingId);

        if ($booking === null) {
            return false;
        }

        return ($type === 'user' && (int) $booking->user_id === $id)
            || ($type === 'driver' && (int) $booking->driver_id === $id);
    }
}
