<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\BookingSettlementService;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Models\CommissionSnapshot;
use App\Models\EventOutbox;
use Illuminate\Support\Facades\DB;
use Throwable;

class RideLifecycleService
{
    public function __construct(
        private BookingSettlementService $settlement,
        private ?EventBus $events = null,
        private ?PlanOverageService $overage = null
    ) {
    }

    public function settle(array $booking, string $paymentMethod)
    {
        if ($this->events === null) {
            return $this->runSettlement($booking, $paymentMethod);
        }

        $connection = (new EventOutbox)->getConnectionName();
        $firstSettlement = false;

        $result = DB::connection($connection)->transaction(function () use ($booking, $paymentMethod, &$firstSettlement) {
            $alreadySettled = CommissionSnapshot::query()
                ->where('booking_id', (int) $booking['booking_id'])
                ->exists();

            $firstSettlement = ! $alreadySettled;

            $transaction = $this->runSettlement($booking, $paymentMethod);

            if ($firstSettlement) {
                $this->emitReleased($booking, $paymentMethod);
            }

            return $transaction;
        });

        // Accrue plan ride-overage AFTER the settlement commits — best-effort,
        // never blocks a settlement. Called while the ride is still pre-completed
        // (settle runs before the COMPLETED transition), so this ride's position
        // is counted correctly by recordRideOverage.
        if ($firstSettlement && $this->overage !== null) {
            try {
                $this->overage->recordRideOverage((int) ($booking['office_id'] ?? 0), (int) $booking['booking_id']);
            } catch (Throwable $e) {
                // overage accrual must never fail a completed ride
            }
        }

        return $result;
    }

    private function runSettlement(array $booking, string $paymentMethod)
    {
        // Collection channels → ledger flows:
        //   cash          → commission debited from the driver's prepaid wallet
        //   wallet        → customer prepaid; release the escrow held at booking
        //   office_wallet → office prepaid (or user, via the rider path); the
        //                   fare was HELD in the booking's ESCROW at creation, so
        //                   it settles by RELEASING that escrow three-ways — NOT
        //                   by `distributeDigital`, which debits fleet PSP_CLEARING
        //                   for money never received and leaves the escrow stuck.
        //                   `releaseRide` debits the booking's escrow owner-
        //                   agnostically, so it is correct whoever funded it.
        //   digital/PSP   → fleet received the fare; distribute it to the wallets
        return match (strtolower($paymentMethod)) {
            'cash' => $this->settlement->settleCash($booking),
            'wallet', 'office_wallet' => $this->settlement->settleWallet($booking),
            default => $this->settlement->settleDigital($booking),
        };
    }

    private function emitReleased(array $booking, string $paymentMethod): void
    {
        $bookingId = (int) $booking['booking_id'];
        $driverId = (int) $booking['driver_id'];
        $officeId = (int) $booking['office_id'];

        $this->events->emit(new DomainEvent(
            EventType::RIDE_RELEASED,
            [Channel::booking($bookingId), Channel::driver($driverId), Channel::office($officeId)],
            [
                'booking_id' => $bookingId,
                'driver_id' => $driverId,
                'office_id' => $officeId,
                'total_minor' => (int) $booking['total_minor'],
                'payment_method' => strtolower($paymentMethod),
            ]
        ));
    }
}
