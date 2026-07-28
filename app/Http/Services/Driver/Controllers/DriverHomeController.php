<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ledger\DriverCurrency;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Repositories\Ledger\DriverStatementRepository;
use App\Http\Services\User\Support\Reply;
use App\Models\DriverPresence;
use App\Models\RideBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Home availability-dock KPIs (`GET /driver/home`) + go-online readiness
 * (`GET /driver/readiness`). Lightweight summaries so the home dock doesn't load
 * the full earnings dashboard.
 */
class DriverHomeController extends Controller
{
    public function __construct(
        private FleetWalletService $wallet,
        private DriverStatementRepository $statement,
    ) {
    }

    /** Real acceptance rate from dispatch offer outcomes; 100 for a new driver. */
    private function acceptanceRate(int $driverId): int
    {
        $o = $this->statement->offerCounts($driverId);
        $decided = (int) $o['accepted'] + (int) $o['rejected'] + (int) $o['expired'];

        return $decided > 0 ? (int) round((int) $o['accepted'] / $decided * 100) : 100;
    }

    public function home(Request $request): JsonResponse
    {
        $driver = $request->user();
        $currency = DriverCurrency::resolve($driver, $request->header('X-Country'));
        $balance = $this->wallet->walletBalanceMinor(OwnerType::DRIVER, (int) $driver->id, $currency);

        $today = RideBooking::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', now()->toDateString());

        // Today's earnings must be what the DRIVER earned, from the same ledger
        // the earnings screen reads. Summing `total_minor` here reported the
        // gross ride fare instead, so home overstated earnings by the commission
        // and disagreed with the earnings screen for the very same day.
        $earnedToday = (int) $this->statement->earnings(
            (int) $driver->id,
            $currency,
            now()->startOfDay()
        )['digital_earnings_minor'];

        // Presence status so the app can rehydrate the availability dock on boot.
        $presence = DriverPresence::query()->where('driver_id', $driver->id)->first();

        return Reply::ok([
            'currency_code' => $currency,
            'today_earned_minor' => $earnedToday,
            'trips_today' => (clone $today)->count(),
            'online_seconds' => $presence?->onlineSecondsToday() ?? 0,
            'wallet_balance_minor' => $balance,
            'acceptanceRate' => $this->acceptanceRate((int) $driver->id),
            'status' => $presence->status ?? 'offline',
            'active_trip' => $this->activeTrip((int) $driver->id),
        ]);
    }

    /**
     * The driver's in-progress booking shaped for the app's
     * `Trip.fromActiveTrip` (nested pickup/dropoff), so reopening the app lands
     * straight back on the live trip. Null when there's no active ride.
     */
    private function activeTrip(int $driverId): ?array
    {
        // Every state in which the driver still owes the rider a ride. This used
        // to hardcode ['assigned','arrived','in_progress'], which both MISSED
        // `arriving` (the state right after accepting) and looked for
        // `in_progress` — a status this system never writes; the real one is
        // `on_trip`. The effect was that reopening the app mid-ride restored no
        // active trip at all, since the driver app rehydrates its trip FSM from
        // exactly this field.
        $b = RideBooking::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', array_merge([BookingStatus::ASSIGNED], BookingStatus::LIVE_SUB))
            ->latest('assigned_at')
            ->first();

        if ($b === null) {
            return null;
        }

        return [
            'booking_id' => (int) $b->id,
            'office_id' => $b->office_id !== null ? (int) $b->office_id : null,
            'service_class' => $b->service_class,
            'payment_method' => $b->payment_method,
            'pricing_style' => $b->pricing_style,
            'status' => $b->status,
            'total_minor' => (int) ($b->total_minor ?? $b->fare_minor ?? 0),
            'currency_code' => $b->currency_code,
            'pickup' => ['lat' => $b->pickup_lat, 'lng' => $b->pickup_lng, 'title' => $b->pickup_title],
            'dropoff' => ['lat' => $b->dropoff_lat, 'lng' => $b->dropoff_lng, 'title' => $b->dropoff_title],
            'stops' => $b->stops ?? [],
        ];
    }

    public function readiness(Request $request): JsonResponse
    {
        $driver = $request->user();
        $vehicle = $driver->vehicleId !== null;
        $office = $driver->officeId !== null;
        $wallet = true; // no blocking dues gate by default

        $passed = ($vehicle ? 1 : 0) + ($office ? 1 : 0) + ($wallet ? 1 : 0);

        return Reply::ok([
            'vehicle' => $vehicle,
            'office' => $office,
            'wallet' => $wallet,
            'ready' => $vehicle && $office && $wallet,
            'checks' => $passed . '/3',
        ]);
    }
}
