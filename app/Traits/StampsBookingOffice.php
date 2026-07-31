<?php

namespace App\Traits;

use App\Models\RideBooking;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Stamps `office_id` on a GLOBAL-table row from the per-shard booking it points
 * at, so the row can be scoped to the office it concerns without the panel having
 * to reach across connections on every read. Best-effort — a row is never blocked
 * when the booking cannot be resolved (it stays null = admin-only).
 */
trait StampsBookingOffice
{
    private static array $hasOfficeColumn = [];

    public static function bootStampsBookingOffice(): void
    {
        static::creating(function ($model) {
            if (! empty($model->office_id) || empty($model->booking_id)) {
                return;
            }

            // A schema that predates the column must still accept the INSERT, so
            // the attribute is only touched once the column is known to exist.
            if (! self::hasOfficeColumn($model)) {
                return;
            }

            $officeId = self::officeOfBooking((int) $model->booking_id);

            if ($officeId !== null) {
                $model->office_id = $officeId;
            }
        });
    }

    private static function hasOfficeColumn($model): bool
    {
        $key = ($model->getConnectionName() ?? '') . ':' . $model->getTable();

        if (! array_key_exists($key, self::$hasOfficeColumn)) {
            try {
                self::$hasOfficeColumn[$key] = Schema::connection($model->getConnectionName())
                    ->hasColumn($model->getTable(), 'office_id');
            } catch (Throwable $e) {
                self::$hasOfficeColumn[$key] = false;
            }
        }

        return self::$hasOfficeColumn[$key];
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
