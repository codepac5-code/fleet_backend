<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\Const\Ride\BookingSource;
use App\Models\LedgerTransaction;

class BookingSettlementService
{
    public function __construct(
        private FleetWalletService $wallet,
        private CommissionResolver $commission
    ) {
    }

    public function settleDigital(array $booking): LedgerTransaction
    {
        // Electronic payment: the fare reached the fleet via the PSP, so the
        // fleet distributes it to the driver/office wallets (keeps its share).
        return $this->wallet->distributeDigital($this->commissionParams($booking));
    }

    public function settleCash(array $booking): LedgerTransaction
    {
        // Cash: the driver holds the fare, so the commission is DEBITED from the
        // driver's prepaid wallet (shortfall → dues).
        return $this->wallet->chargeCommission($this->commissionParams($booking));
    }

    public function settleWallet(array $booking): LedgerTransaction
    {
        // Customer paid from their in-app (prepaid) wallet: the fare was HELD in
        // escrow at booking, so settlement releases it three-ways from escrow
        // (driver + office to their wallets, fleet to revenue).
        return $this->wallet->releaseRide(array_merge($booking, $this->rates($booking)));
    }

    /**
     * Normalise a booking into the commission params both settlement primitives
     * take. The commission base is the amount actually charged (`total_minor`);
     * the resolved rates freeze into the snapshot.
     */
    private function commissionParams(array $booking): array
    {
        $rates = $this->rates($booking);

        return [
            'booking_id' => (int) $booking['booking_id'],
            'driver_id' => (int) $booking['driver_id'],
            'office_id' => (int) ($booking['office_id'] ?? 0),
            'currency_code' => $booking['currency_code'],
            'fare_minor' => (int) $booking['total_minor'],
            'discount_minor' => (int) ($booking['discount_minor'] ?? 0),
            'pricing_style' => $booking['pricing_style'] ?? 'meter',
            'fleet_rate' => (float) $rates['fleet_rate'],
            'office_rate' => (float) ($rates['office_rate'] ?? 0.0),
            'subscription_plan' => $rates['subscription_plan'] ?? null,
        ];
    }

    private function rates(array $booking): array
    {
        $officeId = (int) ($booking['office_id'] ?? 0);

        return ($booking['source'] ?? BookingSource::APP) === BookingSource::OFFICE
            ? $this->commission->forOfficeBooking($officeId)
            : $this->commission->forOffice($officeId);
    }
}
