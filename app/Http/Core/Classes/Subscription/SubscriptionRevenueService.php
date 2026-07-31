<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\LedgerKind;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Models\LedgerTransaction;
use App\Models\OfficeSubscription;
use Throwable;

/**
 * Subscription money, in the ledger.
 *
 * In a subscription country the platform's income IS the subscriptions, yet
 * nothing ever posted them: reports were built purely from per-ride commission
 * snapshots, so a country billing every office monthly reported almost no
 * revenue at all. A paid invoice is now a two-line posting — the money sits in
 * PSP clearing until it is settled out, and the fleet books the revenue.
 */
class SubscriptionRevenueService
{
    public const REFERENCE_TYPE = 'office_subscription';

    public function __construct(private LedgerService $ledger)
    {
    }

    /**
     * Post one paid invoice. Idempotent on the provider invoice id, so a
     * webhook and the hourly reconcile can both deliver it without doubling
     * the revenue.
     */
    public function recordPayment(int $officeId, int $amountMinor, string $currency, ?string $invoiceId, ?int $subscriptionId = null, ?string $planKey = null): ?LedgerTransaction
    {
        if ($officeId <= 0 || $amountMinor <= 0 || $currency === '') {
            return null;
        }

        $key = 'subscription_invoice:' . ($invoiceId ?: ('office_' . $officeId . '_sub_' . $subscriptionId));

        try {
            return $this->ledger->post([
                'idempotency_key' => $key,
                'kind' => LedgerKind::SUBSCRIPTION,
                'currency_code' => strtoupper($currency),
                'reference_type' => self::REFERENCE_TYPE,
                'reference_id' => $subscriptionId,
                'description' => trim('subscription ' . (string) $planKey) . ' — office #' . $officeId,
                'entries' => [
                    [
                        'owner_type' => OwnerType::FLEET,
                        'owner_id' => OwnerType::FLEET_OWNER_ID,
                        'account_type' => AccountType::PSP_CLEARING,
                        'direction' => Direction::DEBIT,
                        'amount_minor' => $amountMinor,
                    ],
                    [
                        'owner_type' => OwnerType::FLEET,
                        'owner_id' => OwnerType::FLEET_OWNER_ID,
                        'account_type' => AccountType::REVENUE,
                        'direction' => Direction::CREDIT,
                        'amount_minor' => $amountMinor,
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            // Revenue bookkeeping must never break the payment flow itself.
            return null;
        }
    }

    public function recordForSubscription(OfficeSubscription $subscription, int $amountMinor, ?string $invoiceId, ?string $currency = null): ?LedgerTransaction
    {
        return $this->recordPayment(
            (int) $subscription->office_id,
            $amountMinor,
            (string) ($currency ?: $subscription->currency_code),
            $invoiceId,
            (int) $subscription->id,
            $subscription->plan_key
        );
    }
}
