<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\LedgerKind;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Models\CommissionSnapshot;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FleetWalletService
{
    public function __construct(private LedgerService $ledger)
    {
    }

    public function topUp(int $userId, int $amountMinor, string $currency, string $idempotencyKey, ?string $referenceType = null, $referenceId = null): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::TOPUP,
            'currency_code' => $currency,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => 'wallet top up',
            'entries' => [
                $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::PSP_CLEARING, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::USER, $userId, AccountType::WALLET, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function holdRide(int $bookingId, int $userId, int $totalMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($totalMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::RIDE_HOLD,
            'currency_code' => $currency,
            'reference_type' => OwnerType::BOOKING,
            'reference_id' => $bookingId,
            'description' => 'ride escrow hold',
            'entries' => [
                $this->line(OwnerType::USER, $userId, AccountType::WALLET, Direction::DEBIT, $totalMinor),
                $this->line(OwnerType::BOOKING, $bookingId, AccountType::ESCROW, Direction::CREDIT, $totalMinor),
            ],
        ]);
    }

    public function holdRideFromOffice(int $bookingId, int $officeId, int $totalMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($totalMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::RIDE_HOLD,
            'currency_code' => $currency,
            'reference_type' => OwnerType::BOOKING,
            'reference_id' => $bookingId,
            'description' => 'office ride escrow hold',
            'entries' => [
                $this->line(OwnerType::OFFICE, $officeId, AccountType::WALLET, Direction::DEBIT, $totalMinor),
                $this->line(OwnerType::BOOKING, $bookingId, AccountType::ESCROW, Direction::CREDIT, $totalMinor),
            ],
        ]);
    }

    public function refundEscrowToOffice(int $bookingId, int $officeId, int $amountMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::REFUND,
            'currency_code' => $currency,
            'reference_type' => OwnerType::BOOKING,
            'reference_id' => $bookingId,
            'description' => 'refund from escrow to office wallet',
            'entries' => [
                $this->line(OwnerType::BOOKING, $bookingId, AccountType::ESCROW, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::OFFICE, $officeId, AccountType::WALLET, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function releaseRide(array $params): LedgerTransaction
    {
        $bookingId = (int) $params['booking_id'];
        $officeId = (int) $params['office_id'];
        $driverId = (int) $params['driver_id'];
        $currency = $params['currency_code'];
        $totalMinor = (int) $params['total_minor'];
        $fleetRate = (float) $params['fleet_rate'];
        $officeRate = (float) $params['office_rate'];
        $fareMinor = (int) ($params['fare_minor'] ?? $totalMinor);
        $discountMinor = (int) ($params['discount_minor'] ?? 0);
        $pricingStyle = $params['pricing_style'] ?? 'meter';
        $subscriptionPlan = $params['subscription_plan'] ?? null;

        $this->assertPositive($totalMinor);

        $split = $this->splitThreeWay($totalMinor, $fleetRate, $officeRate);

        $connection = $this->ledger->connectionName();

        return DB::connection($connection)->transaction(function () use (
            $bookingId, $officeId, $driverId, $currency, $totalMinor, $split,
            $fleetRate, $officeRate, $fareMinor, $discountMinor, $pricingStyle, $subscriptionPlan
        ) {
            $transaction = $this->ledger->post([
                'idempotency_key' => 'release:' . $bookingId,
                'kind' => LedgerKind::RIDE_RELEASE,
                'currency_code' => $currency,
                'reference_type' => OwnerType::BOOKING,
                'reference_id' => $bookingId,
                'description' => 'ride release three way split',
                'entries' => [
                    $this->line(OwnerType::BOOKING, $bookingId, AccountType::ESCROW, Direction::DEBIT, $totalMinor),
                    $this->line(OwnerType::DRIVER, $driverId, AccountType::WALLET, Direction::CREDIT, $split['driver']),
                    $this->line(OwnerType::OFFICE, $officeId, AccountType::WALLET, Direction::CREDIT, $split['office']),
                    $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::REVENUE, Direction::CREDIT, $split['fleet']),
                ],
            ]);

            CommissionSnapshot::query()->firstOrCreate(
                ['booking_id' => $bookingId],
                [
                    'office_id' => $officeId,
                    'driver_id' => $driverId,
                    'currency_code' => $currency,
                    'pricing_style' => $pricingStyle,
                    'fare_minor' => $fareMinor,
                    'discount_minor' => $discountMinor,
                    'total_minor' => $totalMinor,
                    'fleet_rate' => $fleetRate,
                    'office_rate' => $officeRate,
                    'fleet_minor' => $split['fleet'],
                    'office_minor' => $split['office'],
                    'driver_minor' => $split['driver'],
                    'subscription_plan' => $subscriptionPlan,
                ]
            );

            return $transaction;
        });
    }

    public function cashCommission(array $params): LedgerTransaction
    {
        $bookingId = (int) $params['booking_id'];
        $officeId = (int) $params['office_id'];
        $driverId = (int) $params['driver_id'];
        $currency = $params['currency_code'];
        $totalMinor = (int) $params['total_minor'];
        $fleetRate = (float) $params['fleet_rate'];
        $officeRate = (float) $params['office_rate'];
        $fareMinor = (int) ($params['fare_minor'] ?? $totalMinor);
        $discountMinor = (int) ($params['discount_minor'] ?? 0);
        $subscriptionPlan = $params['subscription_plan'] ?? null;

        $this->assertPositive($totalMinor);

        $split = $this->splitThreeWay($totalMinor, $fleetRate, $officeRate);
        $duesMinor = $split['office'] + $split['fleet'];

        $connection = $this->ledger->connectionName();

        return DB::connection($connection)->transaction(function () use (
            $bookingId, $officeId, $driverId, $currency, $totalMinor, $split, $duesMinor,
            $fleetRate, $officeRate, $fareMinor, $discountMinor, $subscriptionPlan
        ) {
            $transaction = $this->ledger->post([
                'idempotency_key' => 'cash:' . $bookingId,
                'kind' => LedgerKind::CASH_COMMISSION,
                'currency_code' => $currency,
                'reference_type' => OwnerType::BOOKING,
                'reference_id' => $bookingId,
                'description' => 'cash trip commission to driver dues',
                'entries' => [
                    $this->line(OwnerType::DRIVER, $driverId, AccountType::DUES, Direction::DEBIT, $duesMinor),
                    $this->line(OwnerType::OFFICE, $officeId, AccountType::REVENUE, Direction::CREDIT, $split['office']),
                    $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::REVENUE, Direction::CREDIT, $split['fleet']),
                ],
            ]);

            CommissionSnapshot::query()->firstOrCreate(
                ['booking_id' => $bookingId],
                [
                    'office_id' => $officeId,
                    'driver_id' => $driverId,
                    'currency_code' => $currency,
                    'pricing_style' => $params['pricing_style'] ?? 'meter',
                    'fare_minor' => $fareMinor,
                    'discount_minor' => $discountMinor,
                    'total_minor' => $totalMinor,
                    'fleet_rate' => $fleetRate,
                    'office_rate' => $officeRate,
                    'fleet_minor' => $split['fleet'],
                    'office_minor' => $split['office'],
                    'driver_minor' => $split['driver'],
                    'subscription_plan' => $subscriptionPlan,
                ]
            );

            return $transaction;
        });
    }

    /**
     * Charge the platform/office commission by DEBITING the driver's prepaid
     * wallet — the driver keeps the full fare (cash in hand, or the digital fare
     * already released to their wallet) and the commission is drawn from their
     * wallet balance.
     *
     * The fleet always takes its share; the office takes its share on top only
     * when the driver belongs to an office (`office_id > 0`). Wallet-first: what
     * the wallet cannot cover becomes driver dues (never a silent failure, never
     * an overdraft), so a cash trip on an underfunded wallet still settles and
     * the shortfall is recorded as debt.
     *
     * Balanced posting:
     *   DR driver wallet (min(commission, wallet balance))
     *   DR driver dues   (the shortfall, if any)
     *   CR fleet revenue (fleet share)
     *   CR office WALLET (office share, if an office driver — the office withdraws
     *                     it later, so it lands in the withdrawable wallet)
     */
    public function chargeCommission(array $params): LedgerTransaction
    {
        $bookingId = (int) $params['booking_id'];
        $driverId = (int) $params['driver_id'];
        $officeId = (int) ($params['office_id'] ?? 0);
        $currency = $params['currency_code'];
        $fareMinor = (int) $params['fare_minor'];
        $fleetRate = (float) $params['fleet_rate'];
        $officeRate = $officeId > 0 ? (float) ($params['office_rate'] ?? 0.0) : 0.0;

        $this->assertPositive($fareMinor);

        $connection = $this->ledger->connectionName();

        return DB::connection($connection)->transaction(function () use (
            $params, $bookingId, $driverId, $officeId, $currency, $fareMinor, $fleetRate, $officeRate
        ) {
            $fleetMinor = (int) round($fareMinor * $fleetRate / 100);
            $officeMinor = (int) round($fareMinor * $officeRate / 100);
            $commissionMinor = $fleetMinor + $officeMinor;

            if ($commissionMinor <= 0) {
                throw new RuntimeException('commission resolves to zero');
            }

            $walletAvailable = max(0, $this->ledger->lockOwnerBalanceMinor(OwnerType::DRIVER, $driverId, AccountType::WALLET, $currency));
            $fromWallet = min($commissionMinor, $walletAvailable);
            $fromDues = $commissionMinor - $fromWallet;

            $entries = [];
            if ($fromWallet > 0) {
                $entries[] = $this->line(OwnerType::DRIVER, $driverId, AccountType::WALLET, Direction::DEBIT, $fromWallet);
            }
            if ($fromDues > 0) {
                $entries[] = $this->line(OwnerType::DRIVER, $driverId, AccountType::DUES, Direction::DEBIT, $fromDues);
            }
            if ($fleetMinor > 0) {
                $entries[] = $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::REVENUE, Direction::CREDIT, $fleetMinor);
            }
            if ($officeMinor > 0) {
                $entries[] = $this->line(OwnerType::OFFICE, $officeId, AccountType::WALLET, Direction::CREDIT, $officeMinor);
            }

            $transaction = $this->ledger->post([
                'idempotency_key' => 'commission:' . $bookingId,
                'kind' => LedgerKind::COMMISSION,
                'currency_code' => $currency,
                'reference_type' => OwnerType::BOOKING,
                'reference_id' => $bookingId,
                'description' => 'ride commission charged from driver wallet',
                'entries' => $entries,
            ]);

            $this->recordCommissionSnapshot($params, $fleetMinor, $officeMinor, $fareMinor - $fleetMinor - $officeMinor);

            return $transaction;
        });
    }

    /**
     * Freeze the commission split for a booking at settlement time (governance:
     * the rates cannot later drift with a settings change). Idempotent per
     * booking — the first settlement wins.
     */
    private function recordCommissionSnapshot(array $params, int $fleetMinor, int $officeMinor, int $driverMinor): void
    {
        $base = (int) $params['fare_minor'];

        CommissionSnapshot::query()->firstOrCreate(
            ['booking_id' => (int) $params['booking_id']],
            [
                'office_id' => (int) ($params['office_id'] ?? 0),
                'driver_id' => (int) $params['driver_id'],
                'currency_code' => $params['currency_code'],
                'pricing_style' => $params['pricing_style'] ?? 'meter',
                'fare_minor' => $base,
                'discount_minor' => (int) ($params['discount_minor'] ?? 0),
                'total_minor' => $base,
                'fleet_rate' => (float) $params['fleet_rate'],
                'office_rate' => (float) ($params['office_rate'] ?? 0.0),
                'fleet_minor' => $fleetMinor,
                'office_minor' => $officeMinor,
                'driver_minor' => $driverMinor,
                'subscription_plan' => $params['subscription_plan'] ?? null,
            ]
        );
    }

    /**
     * Digital ride settlement — the REVERSE direction of {@see chargeCommission}.
     * The customer paid electronically and the money landed with the fleet (via
     * the PSP), so the fleet distributes it: the driver and office wallets are
     * CREDITED their shares, and the fleet keeps its commission as revenue. The
     * driver never pays commission out of their wallet here — it is simply
     * withheld before crediting.
     *
     * Balanced posting:
     *   DR fleet psp_clearing (the full fare received from the PSP)
     *   CR driver wallet   (net = fare − commission)
     *   CR office WALLET    (office share, if an office driver)
     *   CR fleet revenue    (fleet share)
     */
    public function distributeDigital(array $params): LedgerTransaction
    {
        $bookingId = (int) $params['booking_id'];
        $driverId = (int) $params['driver_id'];
        $officeId = (int) ($params['office_id'] ?? 0);
        $currency = $params['currency_code'];
        $fareMinor = (int) $params['fare_minor'];
        $fleetRate = (float) $params['fleet_rate'];
        $officeRate = $officeId > 0 ? (float) ($params['office_rate'] ?? 0.0) : 0.0;

        $this->assertPositive($fareMinor);

        $fleetMinor = (int) round($fareMinor * $fleetRate / 100);
        $officeMinor = (int) round($fareMinor * $officeRate / 100);
        $driverMinor = $fareMinor - $fleetMinor - $officeMinor;

        if ($driverMinor < 0) {
            throw new RuntimeException('commission exceeds fare');
        }

        $entries = [
            $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::PSP_CLEARING, Direction::DEBIT, $fareMinor),
        ];
        if ($driverMinor > 0) {
            $entries[] = $this->line(OwnerType::DRIVER, $driverId, AccountType::WALLET, Direction::CREDIT, $driverMinor);
        }
        if ($officeMinor > 0) {
            $entries[] = $this->line(OwnerType::OFFICE, $officeId, AccountType::WALLET, Direction::CREDIT, $officeMinor);
        }
        if ($fleetMinor > 0) {
            $entries[] = $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::REVENUE, Direction::CREDIT, $fleetMinor);
        }

        $connection = $this->ledger->connectionName();

        return DB::connection($connection)->transaction(function () use ($params, $bookingId, $currency, $entries, $fleetMinor, $officeMinor, $driverMinor) {
            $transaction = $this->ledger->post([
                'idempotency_key' => 'distribute:' . $bookingId,
                'kind' => LedgerKind::RIDE_RELEASE,
                'currency_code' => $currency,
                'reference_type' => OwnerType::BOOKING,
                'reference_id' => $bookingId,
                'description' => 'digital ride distributed from fleet to wallets',
                'entries' => $entries,
            ]);

            $this->recordCommissionSnapshot($params, $fleetMinor, $officeMinor, $driverMinor);

            return $transaction;
        });
    }

    /**
     * Cash-out, first leg: a driver withdraws their wallet at the office they
     * belong to. The office hands the driver cash, so the balance transfers from
     * the driver's wallet into the office's wallet — the office now holds that
     * claim and later withdraws it from the fleet ({@see payout} with an office
     * owner). Fleet-direct drivers withdraw straight from the fleet via payout()
     * instead.
     *
     *   DR driver wallet  → CR office wallet
     */
    public function withdrawDriverToOffice(int $driverId, int $officeId, int $amountMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::PAYOUT,
            'currency_code' => $currency,
            'reference_type' => OwnerType::DRIVER,
            'reference_id' => $driverId,
            'description' => 'driver cash withdrawal at office',
            'entries' => [
                $this->line(OwnerType::DRIVER, $driverId, AccountType::WALLET, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::OFFICE, $officeId, AccountType::WALLET, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function settleDuesFromWallet(int $driverId, int $amountMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::DUES_SETTLE,
            'currency_code' => $currency,
            'reference_type' => OwnerType::DRIVER,
            'reference_id' => $driverId,
            'description' => 'driver dues settled from wallet',
            'entries' => [
                $this->line(OwnerType::DRIVER, $driverId, AccountType::WALLET, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::DRIVER, $driverId, AccountType::DUES, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function payout(string $ownerType, int $ownerId, string $sourceAccountType, int $amountMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        if (!in_array($sourceAccountType, [AccountType::WALLET, AccountType::REVENUE], true)) {
            throw new RuntimeException('payout source must be wallet or revenue');
        }

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::PAYOUT,
            'currency_code' => $currency,
            'reference_type' => $ownerType,
            'reference_id' => $ownerId,
            'description' => 'payout to bank',
            'entries' => [
                $this->line($ownerType, $ownerId, $sourceAccountType, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::PAYOUT_CLEARING, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function refundFromEscrow(int $bookingId, int $userId, int $amountMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::REFUND,
            'currency_code' => $currency,
            'reference_type' => OwnerType::BOOKING,
            'reference_id' => $bookingId,
            'description' => 'refund from escrow to wallet',
            'entries' => [
                $this->line(OwnerType::BOOKING, $bookingId, AccountType::ESCROW, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::USER, $userId, AccountType::WALLET, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function refundFromFleet(int $bookingId, int $userId, int $amountMinor, string $currency, string $idempotencyKey): LedgerTransaction
    {
        $this->assertPositive($amountMinor);

        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::REFUND,
            'currency_code' => $currency,
            'reference_type' => OwnerType::BOOKING,
            'reference_id' => $bookingId,
            'description' => 'refund from fleet revenue to wallet',
            'entries' => [
                $this->line(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, AccountType::REVENUE, Direction::DEBIT, $amountMinor),
                $this->line(OwnerType::USER, $userId, AccountType::WALLET, Direction::CREDIT, $amountMinor),
            ],
        ]);
    }

    public function adjustment(array $entries, string $currency, string $idempotencyKey, ?string $description = null, ?string $referenceType = null, $referenceId = null): LedgerTransaction
    {
        return $this->ledger->post([
            'idempotency_key' => $idempotencyKey,
            'kind' => LedgerKind::ADJUSTMENT,
            'currency_code' => $currency,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description ?? 'manual adjustment',
            'entries' => $entries,
        ]);
    }

    public function walletBalanceMinor(string $ownerType, int $ownerId, string $currency): int
    {
        return $this->ledger->ownerBalanceMinor($ownerType, $ownerId, AccountType::WALLET, $currency);
    }

    public function lockWalletBalanceMinor(string $ownerType, int $ownerId, string $currency): int
    {
        return $this->ledger->lockOwnerBalanceMinor($ownerType, $ownerId, AccountType::WALLET, $currency);
    }

    public function duesBalanceMinor(int $driverId, string $currency): int
    {
        return $this->ledger->ownerBalanceMinor(OwnerType::DRIVER, $driverId, AccountType::DUES, $currency);
    }

    public function revenueBalanceMinor(string $ownerType, int $ownerId, string $currency): int
    {
        return $this->ledger->ownerBalanceMinor($ownerType, $ownerId, AccountType::REVENUE, $currency);
    }

    public function escrowBalanceMinor(int $bookingId, string $currency): int
    {
        return $this->ledger->ownerBalanceMinor(OwnerType::BOOKING, $bookingId, AccountType::ESCROW, $currency);
    }

    public function splitThreeWay(int $totalMinor, float $fleetRate, float $officeRate): array
    {
        $driverRate = 100 - $fleetRate - $officeRate;

        if ($driverRate < 0) {
            throw new RuntimeException('fleet + office commission exceeds 100 percent');
        }

        $split = Money::splitByRates($totalMinor, [
            'fleet' => $fleetRate,
            'office' => $officeRate,
            'driver' => $driverRate,
        ]);

        return $split;
    }

    private function line(string $ownerType, int $ownerId, string $accountType, string $direction, int $amountMinor): array
    {
        return [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'account_type' => $accountType,
            'direction' => $direction,
            'amount_minor' => $amountMinor,
        ];
    }

    private function assertPositive(int $amountMinor): void
    {
        if ($amountMinor <= 0) {
            throw new RuntimeException('amount must be positive');
        }
    }
}
