<?php

namespace App\Http\Core\Repositories\Ledger;

use App\Models\CommissionSnapshot;
use App\Models\DispatchOffer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentDriverStatementRepository implements DriverStatementRepository
{
    /**
     * Earnings for [currency], falling back to the currency the driver was
     * ACTUALLY paid in when that yields nothing.
     *
     * The currency argument comes from `DriverCurrency::resolve()`, i.e. the
     * driver's country — while a commission snapshot carries the currency of the
     * OFFICE's tariff. Those disagree whenever an office prices in another
     * currency (office 1 prices in SAR while the QA driver resolves to QAR), and
     * the filter then matched zero rows: a driver who had completed a cash trip
     * saw `trips: 0` and every figure at zero, while `driver/home` counted the
     * same trip as 1. The money was recorded correctly; only the report was
     * blind to it, which is the worst shape for an earnings bug to take.
     *
     * A report must never silently hide money it holds. So when the requested
     * currency has no rows we report the driver's most recent snapshot currency
     * instead, and return which currency that was — the caller already surfaces
     * `currency_code`, so the screen stays truthful.
     */
    public function earnings(int $driverId, string $currency, ?string $sinceIso): array
    {
        $actual = $this->currencyWithData($driverId, $currency, $sinceIso);

        if ($actual !== null && $actual !== $currency) {
            $currency = $actual;
        }

        $query = CommissionSnapshot::query()
            ->from('commission_snapshots as cs')
            ->join('ride_bookings as rb', 'rb.id', '=', 'cs.booking_id')
            ->where('cs.driver_id', $driverId)
            ->where('cs.currency_code', $currency);

        if ($sinceIso !== null) {
            $query->where('cs.created_at', '>=', $sinceIso);
        }

        $row = $query->selectRaw('
            count(*) as trips,
            coalesce(sum(cs.total_minor), 0) as gross_minor,
            coalesce(sum(cs.fleet_minor + cs.office_minor), 0) as fees_minor,
            coalesce(sum(cs.driver_minor), 0) as driver_earned_minor,
            coalesce(sum(case when rb.payment_method = ? then cs.total_minor else 0 end), 0) as cash_collected_minor,
            coalesce(sum(case when rb.payment_method = ? then cs.fleet_minor + cs.office_minor else 0 end), 0) as cash_due_to_office_minor,
            coalesce(sum(case when rb.payment_method != ? then cs.driver_minor else 0 end), 0) as digital_earnings_minor
        ', ['cash', 'cash', 'cash'])->first();

        return [
            // The currency these figures are actually in — which is NOT always
            // the one that was asked for (see the note above).
            'currency_code' => $currency,
            'trips' => (int) $row->trips,
            'gross_minor' => (int) $row->gross_minor,
            'fees_minor' => (int) $row->fees_minor,
            'driver_earned_minor' => (int) $row->driver_earned_minor,
            'cash_collected_minor' => (int) $row->cash_collected_minor,
            'cash_due_to_office_minor' => (int) $row->cash_due_to_office_minor,
            'digital_earnings_minor' => (int) $row->digital_earnings_minor,
        ];
    }

    /**
     * The currency to actually report in: [preferred] when it has rows, else the
     * driver's most recent snapshot currency, else null (no data at all).
     */
    private function currencyWithData(int $driverId, string $preferred, ?string $sinceIso): ?string
    {
        $base = fn () => CommissionSnapshot::query()
            ->where('driver_id', $driverId)
            ->when($sinceIso !== null, fn ($q) => $q->where('created_at', '>=', $sinceIso));

        if ($base()->where('currency_code', $preferred)->exists()) {
            return $preferred;
        }

        return $base()->orderByDesc('id')->value('currency_code');
    }

    public function completedTrips(int $driverId, ?int $cursorId, int $limit): Collection
    {
        $query = DB::connection((new CommissionSnapshot)->getConnectionName())
            ->table('commission_snapshots as cs')
            ->join('ride_bookings as rb', 'rb.id', '=', 'cs.booking_id')
            ->where('cs.driver_id', $driverId);

        if ($cursorId !== null) {
            $query->where('cs.id', '<', $cursorId);
        }

        return collect($query->orderByDesc('cs.id')->limit($limit + 1)->get([
            'cs.id as snapshot_id', 'cs.booking_id', 'cs.total_minor', 'cs.driver_minor',
            'cs.currency_code', 'cs.pricing_style', 'cs.created_at',
            'rb.service', 'rb.service_class', 'rb.payment_method', 'rb.pickup_title', 'rb.dropoff_title',
        ]));
    }

    public function offerCounts(int $driverId): array
    {
        $rows = DispatchOffer::query()
            ->where('driver_id', $driverId)
            ->selectRaw('status, count(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');

        return [
            'accepted' => (int) ($rows['accepted'] ?? 0),
            'rejected' => (int) ($rows['rejected'] ?? 0),
            'expired' => (int) ($rows['expired'] ?? 0),
            'offered' => (int) ($rows['offered'] ?? 0),
        ];
    }

    public function tripDetail(int $driverId, int $bookingId): ?object
    {
        return DB::connection((new CommissionSnapshot)->getConnectionName())
            ->table('commission_snapshots as cs')
            ->join('ride_bookings as rb', 'rb.id', '=', 'cs.booking_id')
            ->where('cs.driver_id', $driverId)
            ->where('cs.booking_id', $bookingId)
            ->first([
                'cs.booking_id', 'cs.total_minor', 'cs.fare_minor', 'cs.discount_minor', 'cs.driver_minor',
                'cs.fleet_minor', 'cs.office_minor', 'cs.currency_code', 'cs.pricing_style', 'cs.created_at',
                'rb.service', 'rb.service_class', 'rb.payment_method', 'rb.office_id',
                'rb.pickup_title', 'rb.dropoff_title', 'rb.distance_m', 'rb.duration_s',
            ]);
    }
}
