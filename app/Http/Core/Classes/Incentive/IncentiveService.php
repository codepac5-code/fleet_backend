<?php

namespace App\Http\Core\Classes\Incentive;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\DriverIncentive;
use App\Models\DriverIncentiveProgress;
use Carbon\Carbon;
use Throwable;

/**
 * Driver incentives: admin-defined ride targets over a day/week/month window,
 * paid into the driver's wallet from fleet revenue the moment the target is met.
 *
 * Progress is counted forward from completed rides rather than recomputed from
 * history, so a rule launched today starts everyone at zero — an incentive is a
 * promise about what happens NEXT, not a retroactive payout.
 */
class IncentiveService
{
    public function __construct(private FleetWalletService $wallet)
    {
    }

    /**
     * Counts one completed ride against every active rule and pays out any that
     * just hit their target. Best-effort — never allowed to fail a finished ride.
     */
    public function recordRide(int $driverId, int $bookingId): array
    {
        if ($driverId <= 0) {
            return [];
        }

        try {
            $rules = DriverIncentive::query()->where('is_active', true)->get();
        } catch (Throwable $e) {
            return [];
        }

        $awarded = [];

        foreach ($rules as $rule) {
            try {
                $progress = $this->advance($rule, $driverId);

                if ($progress !== null && $progress->rewarded) {
                    $awarded[] = $progress;
                }
            } catch (Throwable $e) {
                continue;
            }
        }

        return $awarded;
    }

    /** What the driver app shows: every active rule with this driver's progress. */
    public function progressFor(int $driverId): array
    {
        try {
            $rules = DriverIncentive::query()->where('is_active', true)->orderBy('target_rides')->get();
        } catch (Throwable $e) {
            return [];
        }

        $currency = ShardManager::currency();

        return $rules->map(function (DriverIncentive $rule) use ($driverId, $currency) {
            $period = $this->period($rule->window);
            $progress = $this->progressRow($rule, $driverId, $period, false);
            $rides = $progress !== null ? (int) $progress->rides : 0;

            return [
                'id' => (int) $rule->id,
                'name' => (app()->getLocale() ?: 'en') === 'ar' ? $rule->name_ar : $rule->name_en,
                'window' => $rule->window,
                'period' => $period,
                'target' => (int) $rule->target_rides,
                'rides' => min($rides, (int) $rule->target_rides),
                'remaining' => max(0, (int) $rule->target_rides - $rides),
                'rewardMinor' => (int) $rule->reward_minor,
                'currency' => $currency,
                'rewarded' => $progress !== null && (bool) $progress->rewarded,
                'endsAt' => $this->windowEnd($rule->window)->toIso8601String(),
            ];
        })->all();
    }

    private function advance(DriverIncentive $rule, int $driverId): ?DriverIncentiveProgress
    {
        $period = $this->period($rule->window);
        $progress = $this->progressRow($rule, $driverId, $period, true);

        if ($progress === null || $progress->rewarded) {
            return $progress;
        }

        $progress->rides = (int) $progress->rides + 1;

        if ($progress->rides >= (int) $rule->target_rides && (int) $rule->reward_minor > 0) {
            $currency = ShardManager::currency();

            // Funded from fleet revenue, like any other bonus the fleet promises.
            // Idempotency key is the rule + driver + period, so the reward can be
            // granted exactly once per window no matter how the rides land.
            $this->wallet->adjustment(
                [
                    ['owner_type' => OwnerType::FLEET, 'owner_id' => OwnerType::FLEET_OWNER_ID, 'account_type' => AccountType::REVENUE, 'direction' => Direction::DEBIT, 'amount_minor' => (int) $rule->reward_minor],
                    ['owner_type' => OwnerType::DRIVER, 'owner_id' => $driverId, 'account_type' => AccountType::WALLET, 'direction' => Direction::CREDIT, 'amount_minor' => (int) $rule->reward_minor],
                ],
                $currency,
                'incentive:' . $rule->id . ':' . $driverId . ':' . $period,
                'driver incentive reward',
                'driver_incentive',
                (int) $rule->id
            );

            $progress->rewarded = true;
            $progress->reward_minor = (int) $rule->reward_minor;
            $progress->currency_code = $currency;
            $progress->rewarded_at = Carbon::now();
        }

        $progress->save();

        return $progress;
    }

    private function progressRow(DriverIncentive $rule, int $driverId, string $period, bool $create): ?DriverIncentiveProgress
    {
        $existing = DriverIncentiveProgress::query()
            ->where('incentive_id', $rule->id)
            ->where('driver_id', $driverId)
            ->where('period', $period)
            ->first();

        if ($existing !== null || ! $create) {
            return $existing;
        }

        return DriverIncentiveProgress::query()->create([
            'incentive_id' => (int) $rule->id,
            'driver_id' => $driverId,
            'period' => $period,
            'rides' => 0,
            'rewarded' => false,
        ]);
    }

    /** The window a ride right now belongs to. */
    public function period(string $window, ?Carbon $at = null): string
    {
        $at ??= Carbon::now();

        return match ($window) {
            DriverIncentive::WINDOW_DAY => $at->format('Y-m-d'),
            DriverIncentive::WINDOW_MONTH => $at->format('Y-m'),
            default => $at->format('o-\WW'),
        };
    }

    private function windowEnd(string $window, ?Carbon $at = null): Carbon
    {
        $at ??= Carbon::now();

        return match ($window) {
            DriverIncentive::WINDOW_DAY => $at->copy()->endOfDay(),
            DriverIncentive::WINDOW_MONTH => $at->copy()->endOfMonth(),
            default => $at->copy()->endOfWeek(),
        };
    }
}
