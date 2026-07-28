<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Event\BookingEvents;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Core\Repositories\Ride\RideBookingRepository;
use App\Models\RideBooking;
use Illuminate\Support\Carbon;

class ScheduledActivationService
{
    public function __construct(
        private RideBookingRepository $bookings,
        private FleetWalletService $wallet,
        private DispatchService $dispatch,
        private ?EventBus $events = null
    ) {
    }

    public function activateDue(int $leadSeconds, int $limit = 100): int
    {
        $before = Carbon::now()->addSeconds($leadSeconds)->toDateTimeString();
        $count = 0;

        foreach ($this->bookings->dueScheduled($before, $limit) as $booking) {
            $this->activate($booking);
            $count++;
        }

        return $count;
    }

    private function activate(RideBooking $booking): void
    {
        $this->bookings->transaction(function () use ($booking) {
            if (strtolower((string) $booking->payment_method) !== 'cash' && (int) $booking->total_minor > 0) {
                $held = $this->wallet->escrowBalanceMinor((int) $booking->id, $booking->currency_code);

                if ($held <= 0) {
                    $balance = $this->wallet->lockWalletBalanceMinor(OwnerType::USER, (int) $booking->user_id, $booking->currency_code);

                    if ((int) $booking->total_minor <= $balance) {
                        $this->wallet->holdRide((int) $booking->id, (int) $booking->user_id, (int) $booking->total_minor, $booking->currency_code, 'hold:' . $booking->id);
                        $booking->held_minor = (int) $booking->total_minor;
                    }
                }
            }

            $this->dispatch->createJob((int) $booking->id, (int) $booking->office_id, $booking->service_class, (float) $booking->pickup_lat, (float) $booking->pickup_lng);
            $this->dispatch->offerWave((int) $booking->id);

            $booking->status = BookingStatus::MATCHING;
            $this->bookings->save($booking);

            $this->emitStatus($booking);
        });
    }

    private function emitStatus(RideBooking $booking): void
    {
        if ($this->events === null) {
            return;
        }

        $this->events->emit(BookingEvents::statusChanged($booking));
    }
}
