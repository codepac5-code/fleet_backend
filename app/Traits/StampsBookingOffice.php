<?php

namespace App\Traits;

use App\Models\RideBooking;
use Throwable;

/**
 * Stamps `office_id` on a GLOBAL-table row from the per-shard booking it points
 * at, so the row can be scoped to the office it concerns without the panel having
 * to reach across connections on every read. Best-effort — a row is never blocked
 * when the booking cannot be resolved (it stays null = admin-only).
 */
trait StampsBookingOffice
{
    public static function bootStampsBookingOffice(): void
    {
        static::creating(function ($model) {
            if (! empty($model->office_id) || empty($model->booking_id)) {
                return;
            }

            $officeId = self::officeOfBooking((int) $model->booking_id);

            if ($officeId !== null) {
                $model->office_id = $officeId;
            }
        });
    }

    public static function officeOfBooking(int $bookingId): ?int
    {
        try {
            $officeId = RideBooking::query()->whereKey($bookingId)->value('office_id');

            return $officeId !== null ? (int) $officeId : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
