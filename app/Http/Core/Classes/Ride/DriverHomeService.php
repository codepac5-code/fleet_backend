<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Repositories\Dispatch\DispatchJobRepository;
use App\Http\Core\Repositories\Ledger\DriverStatementRepository;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\DriverPresence;
use Illuminate\Support\Carbon;

class DriverHomeService
{
    public function __construct(
        private DriverStatementRepository $statement,
        private FleetWalletService $wallet,
        private DispatchJobRepository $jobs,
        private RideBookingRepository $bookings
    ) {
    }

    public function home(int $driverId, string $currency): array
    {
        $today = $this->statement->earnings($driverId, $currency, Carbon::today()->toDateTimeString());
        $presence = DriverPresence::query()->where('driver_id', $driverId)->first();

        return [
            'status' => $presence !== null ? $presence->status : 'offline',
            'today' => [
                'earnings_minor' => $today['driver_earned_minor'],
                'trips' => $today['trips'],
            ],
            'wallet_balance_minor' => $this->wallet->walletBalanceMinor(OwnerType::DRIVER, $driverId, $currency),
            'currency_code' => $currency,
            'active_trip' => $this->activeTrip($driverId),
        ];
    }

    private function activeTrip(int $driverId): ?array
    {
        $job = $this->jobs->currentAssignment($driverId);

        if ($job === null) {
            return null;
        }

        $booking = $this->bookings->find((int) $job->booking_id);

        if ($booking === null) {
            return null;
        }

        return [
            'booking_id' => (int) $booking->id,
            'status' => $booking->status,
            'service' => $booking->service,
            'service_class' => $booking->service_class,
            'payment_method' => $booking->payment_method,
            'total_minor' => (int) $booking->total_minor,
            'currency_code' => $booking->currency_code,
            'pickup' => ['lat' => (float) $booking->pickup_lat, 'lng' => (float) $booking->pickup_lng, 'title' => $booking->pickup_title],
            'dropoff' => ['lat' => (float) $booking->dropoff_lat, 'lng' => (float) $booking->dropoff_lng, 'title' => $booking->dropoff_title],
            'channel' => Channel::booking((int) $booking->id),
        ];
    }
}
