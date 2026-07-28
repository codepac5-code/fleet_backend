<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\User\Support\Presenters\MoneyPresenter;
use App\Models\InfrastructureNode;
use Illuminate\Console\Command;
use Throwable;

class WalletCredit extends Command
{
    protected $signature = 'fleet:wallet-credit {user : rider user id} {amount : amount in MAJOR units, e.g. 500} {--currency= : ISO code, defaults to the shard default} {--country= : ISO2 country to activate that shard first} {--minor : treat amount as MINOR units instead of major}';

    protected $description = 'Dev/testing: credit a rider wallet directly through the ledger (bypasses the PSP), so wallet-payment flows can be exercised without a live Stripe.';

    public function handle(FleetWalletService $wallet): int
    {
        $country = $this->option('country');

        if ($country) {
            $node = InfrastructureNode::query()
                ->where('type', 'country')
                ->whereRaw('LOWER(country_code) = ?', [strtolower($country)])
                ->first();

            if (! $node) {
                $this->error(sprintf('No country shard found for "%s".', $country));

                return self::FAILURE;
            }

            ShardManager::activate($node);
            $this->line(sprintf('Activated shard %s.', $node->name ?? $node->country_code));
        }

        $userId = (int) $this->argument('user');
        $currency = MoneyPresenter::currency($this->option('currency') !== null ? (string) $this->option('currency') : null);

        $amount = (float) $this->argument('amount');
        $amountMinor = $this->option('minor')
            ? (int) round($amount)
            : (int) round($amount * (10 ** (int) $currency['decimals']));

        if ($amountMinor <= 0) {
            $this->error('Amount must be positive.');

            return self::FAILURE;
        }

        $key = 'devcredit:' . $userId . ':' . $amountMinor . ':' . time();

        try {
            $tx = $wallet->topUp($userId, $amountMinor, $currency['code'], $key, 'dev_credit', $userId);

            $balance = $wallet->walletBalanceMinor(OwnerType::USER, $userId, $currency['code']);

            $this->info(sprintf(
                'Credited user %d with %s %s (minor %d). Ledger tx #%d.',
                $userId,
                MoneyPresenter::decimal($amountMinor, (int) $currency['decimals']),
                $currency['code'],
                $amountMinor,
                $tx->id
            ));
            $this->line(sprintf('New wallet balance: %s %s.', MoneyPresenter::decimal($balance, (int) $currency['decimals']), $currency['code']));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Credit failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
