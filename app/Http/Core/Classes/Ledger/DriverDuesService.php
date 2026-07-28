<?php

namespace App\Http\Core\Classes\Ledger;

use RuntimeException;

class DriverDuesService
{
    public function __construct(private FleetWalletService $wallet)
    {
    }

    public function outstanding(int $driverId, string $currency): int
    {
        return $this->wallet->duesBalanceMinor($driverId, $currency);
    }

    public function settleFromWallet(int $driverId, ?int $amountMinor, string $currency, string $idempotencyKey): array
    {
        $dues = $this->wallet->duesBalanceMinor($driverId, $currency);

        if ($dues <= 0) {
            throw new RuntimeException('no_dues');
        }

        $amount = $amountMinor ?? $dues;

        if ($amount <= 0) {
            throw new RuntimeException('invalid_amount');
        }

        if ($amount > $dues) {
            $amount = $dues;
        }

        $balance = $this->wallet->walletBalanceMinor('driver', $driverId, $currency);

        if ($amount > $balance) {
            throw new RuntimeException('insufficient_balance');
        }

        $transaction = $this->wallet->settleDuesFromWallet($driverId, $amount, $currency, $idempotencyKey);

        return [
            'driver_id' => $driverId,
            'settled_minor' => $amount,
            'remaining_dues_minor' => $this->wallet->duesBalanceMinor($driverId, $currency),
            'currency_code' => $currency,
            'ledger_transaction_uuid' => $transaction->uuid,
        ];
    }
}
