<?php

namespace App\Http\Core\Classes\Ride;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use RuntimeException;

class BookingHoldService
{
    public function __construct(
        private TariffResolver $tariffs,
        private PricingService $pricing,
        private FleetWalletService $wallet
    ) {
    }

    public function hold(int $bookingId, int $userId, int $officeId, string $serviceClass, float $distanceMeters, int $durationSeconds): array
    {
        $tariff = $this->tariffs->forOffice($officeId, $serviceClass);

        if ($tariff === null) {
            throw new RuntimeException('tariff_not_found');
        }

        $currency = $tariff['currency_code'];
        $quote = $this->pricing->quote($tariff, $distanceMeters, $durationSeconds);
        $fare = (int) $quote['fare_minor'];

        $alreadyHeld = $this->wallet->escrowBalanceMinor($bookingId, $currency);

        if ($alreadyHeld > 0) {
            return [
                'booking_id' => $bookingId,
                'held_minor' => $alreadyHeld,
                'currency_code' => $currency,
                'already_held' => true,
            ];
        }

        if ($fare <= 0) {
            throw new RuntimeException('fare must be positive');
        }

        $balance = $this->wallet->walletBalanceMinor('user', $userId, $currency);

        if ($fare > $balance) {
            throw new RuntimeException('insufficient_balance');
        }

        $this->wallet->holdRide($bookingId, $userId, $fare, $currency, 'hold:' . $bookingId);

        return [
            'booking_id' => $bookingId,
            'held_minor' => $fare,
            'currency_code' => $currency,
            'already_held' => false,
        ];
    }
}
