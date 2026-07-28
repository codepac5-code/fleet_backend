<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Repositories\Ledger\DriverStatementRepository;
use App\Http\Core\Repositories\Rating\RideRatingRepository;
use App\Models\DriverPresence;
use App\Models\RideBooking;
use Illuminate\Support\Carbon;

class DriverEarningsService
{
    public function __construct(
        private DriverStatementRepository $statement,
        private FleetWalletService $wallet,
        private DriverDuesService $dues,
        private RideRatingRepository $ratings
    ) {
    }

    public function summary(int $driverId, string $period, string $currency): array
    {
        $since = $this->since($period);
        $e = $this->statement->earnings($driverId, $currency, $since);

        $walletBalance = $this->wallet->walletBalanceMinor('driver', $driverId, $currency);
        $duesBalance = $this->dues->outstanding($driverId, $currency);
        $rating = $this->ratings->aggregateFor('driver', $driverId);

        return [
            'performance' => $this->performance($driverId, $currency),
            'period' => $period,
            // The repository reports the currency the driver was ACTUALLY paid
            // in, which differs from the country-resolved one when the office
            // prices in another currency. Echoing the requested currency here
            // would label real SAR figures as QAR.
            'currency_code' => $e['currency_code'] ?? $currency,
            'trips' => $e['trips'],
            'gross_minor' => $e['gross_minor'],
            'cash_collected_minor' => $e['cash_collected_minor'],
            'digital_earnings_minor' => $e['digital_earnings_minor'],
            'fees_minor' => $e['fees_minor'],
            'cash_due_to_office_minor' => $e['cash_due_to_office_minor'],
            'adjustments_minor' => 0,
            'net_expected_payout_minor' => $walletBalance - $duesBalance,
            'wallet_balance_minor' => $walletBalance,
            'dues_balance_minor' => $duesBalance,
            'avg_fare_minor' => $e['trips'] > 0 ? intdiv($e['gross_minor'], $e['trips']) : 0,
            'rating' => ['average' => $rating['average'], 'count' => $rating['count']],
            'chart_minor' => $this->chart($driverId),
            ...$this->timeProductivity($driverId),
        ];
    }

    /**
     * Today-basis productivity: real online hours (from presence session
     * accumulation) and the trips/earnings-per-hour derived from them. Returns
     * `online_hours` as null when the driver has no online time today, so the
     * app falls back to its static copy rather than showing a divide-by-zero.
     *
     * @return array<string, mixed>
     */
    private function timeProductivity(int $driverId): array
    {
        $presence = DriverPresence::query()->find($driverId);
        $seconds = $presence !== null ? $presence->onlineSecondsToday() : 0;
        $hours = $seconds / 3600;

        $today = RideBooking::query()
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereDate('completed_at', Carbon::today()->toDateString());
        $tripsToday = (clone $today)->count();
        $grossToday = (int) (clone $today)->sum('total_minor');

        return [
            'online_seconds' => $seconds,
            'online_hours' => $seconds > 0
                ? intdiv($seconds, 3600) . 'h ' . intdiv($seconds % 3600, 60) . 'm'
                : null,
            'trips_per_hour' => $hours > 0 ? round($tripsToday / $hours, 1) : null,
            'earnings_per_hour_minor' => $hours > 0 ? (int) round($grossToday / $hours) : null,
        ];
    }

    /**
     * Daily gross (minor units) for the last 7 days, oldest → newest, for the
     * home/earnings hero chart. Zero-filled so the app always gets 7 bars.
     *
     * @return array<int, int>
     */
    private function chart(int $driverId): array
    {
        $start = Carbon::today()->subDays(6);

        $rows = RideBooking::query()
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '>=', $start->toDateString())
            ->get(['completed_at', 'total_minor']);

        $buckets = array_fill(0, 7, 0);
        foreach ($rows as $row) {
            $idx = (int) $start->diffInDays(Carbon::parse($row->completed_at)->startOfDay(), false);
            if ($idx >= 0 && $idx < 7) {
                $buckets[$idx] += (int) $row->total_minor;
            }
        }

        return $buckets;
    }

    private function performance(int $driverId, string $currency): array
    {
        $offers = $this->statement->offerCounts($driverId);
        $decided = $offers['accepted'] + $offers['rejected'] + $offers['expired'];
        $completed = $this->statement->earnings($driverId, $currency, null)['trips'];

        // Cancellation rate: cancelled / (completed + cancelled), all-time.
        $cancelled = RideBooking::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', ['cancelled', 'no_show'])
            ->count();
        $finished = $completed + $cancelled;

        return [
            'offers' => $offers,
            'acceptance_rate' => $decided > 0 ? round($offers['accepted'] / $decided * 100, 1) : 0.0,
            'completion_rate' => $offers['accepted'] > 0 ? round(min($completed, $offers['accepted']) / $offers['accepted'] * 100, 1) : 0.0,
            'cancellation_rate' => $finished > 0 ? round($cancelled / $finished * 100, 1) : 0.0,
            'on_time_pickup' => $this->onTimePickup($driverId),
        ];
    }

    /**
     * On-time-pickup %: of scheduled bookings the driver reached (`arrived_at`
     * set), the share where arrival was no later than `scheduled_at` + 5 min.
     * Null when there are no measurable scheduled pickups (app keeps its static
     * copy). Only scheduled trips carry a committed pickup time.
     */
    private function onTimePickup(int $driverId): ?float
    {
        $rows = RideBooking::query()
            ->where('driver_id', $driverId)
            ->whereNotNull('scheduled_at')
            ->whereNotNull('arrived_at')
            ->get(['scheduled_at', 'arrived_at']);

        if ($rows->isEmpty()) {
            return null;
        }

        $onTime = 0;
        foreach ($rows as $row) {
            $target = Carbon::parse($row->scheduled_at)->addMinutes(5);
            if (Carbon::parse($row->arrived_at)->lessThanOrEqualTo($target)) {
                $onTime++;
            }
        }

        return round($onTime / $rows->count() * 100, 1);
    }

    private function since(string $period): ?string
    {
        return match ($period) {
            'today' => Carbon::today()->toDateTimeString(),
            'week' => Carbon::now()->startOfWeek()->toDateTimeString(),
            'month' => Carbon::now()->startOfMonth()->toDateTimeString(),
            default => null,
        };
    }
}
