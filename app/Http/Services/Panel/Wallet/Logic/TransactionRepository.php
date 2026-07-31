<?php

namespace App\Http\Services\Panel\Wallet\Logic;

use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\LedgerKind;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use App\Models\Office;
use App\Models\OfficeSubscription;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Reads the REAL double-entry ledger (`ledger_transactions` / `ledger_entries`),
 * NOT the legacy `wallet_transactions` template table (whose auto-generated
 * "Transfer from Wallet #3 to Wallet #7 for commission" rows were misleading —
 * raw owner ids and a rider→driver line mislabelled as commission).
 *
 * Each ledger transaction is presented as one row with a readable from→to
 * (largest debit party → largest credit party), the summed amount, and a
 * kind-based description, so the panel shows money as it actually moved.
 */
class TransactionRepository
{
    public function __construct(private EntityScope $scope) {}

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function base(): Builder
    {
        $query = LedgerTransaction::on($this->connection())->newQuery();

        if (! $this->scope->isAdmin()) {
            $officeId = (int) $this->scope->officeId();

            // Only transactions that touch one of this office's ledger accounts.
            $query->whereIn('id', function (QueryBuilder $sub) use ($officeId) {
                $sub->select('e.transaction_id')
                    ->from('ledger_entries as e')
                    ->join('ledger_accounts as a', 'a.id', '=', 'e.account_id')
                    ->where('a.owner_type', OwnerType::OFFICE)
                    ->where('a.owner_id', $officeId);
            });
        }

        return $query;
    }

    public function paginate(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->base()
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $w) use ($search) {
                    $w->where('description', 'like', "%{$search}%")
                        ->orWhere('uuid', 'like', "%{$search}%")
                        ->orWhere('reference_id', $search)
                        ->orWhere('id', $search);
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $this->enrich($paginator->getCollection());

        return $paginator;
    }

    /** CSV rows for the current scope (per-country, office-scoped) — no paging. */
    public function exportRows(int $limit = 20000): array
    {
        $items = $this->base()->latest('id')->limit($limit)->get();
        $this->enrich($items);

        return $items->map(fn ($t) => [
            $t->id,
            $t->uuid,
            $t->amount,
            $t->kind,
            $t->from_name,
            $t->to_name,
            optional($t->created_at)->format('Y-m-d H:i'),
        ])->all();
    }

    private function enrich(Collection $items): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $conn = $this->connection();
        $txIds = $items->pluck('id')->all();

        $entriesByTx = LedgerEntry::on($conn)
            ->whereIn('transaction_id', $txIds)
            ->get(['transaction_id', 'account_id', 'direction', 'amount_minor'])
            ->groupBy('transaction_id');

        $accountIds = $entriesByTx->flatten(1)->pluck('account_id')->unique()->filter()->all();
        $accounts = LedgerAccount::on($conn)->whereIn('id', $accountIds)->get()->keyBy('id');

        // Resolve owner display names in bulk, per owner type.
        $ownerIds = [];
        foreach ($accounts as $a) {
            $ownerIds[$a->owner_type][] = (int) $a->owner_id;
        }
        $names = [];
        foreach ($ownerIds as $type => $ids) {
            $names[$type] = $this->resolveNames($type, array_values(array_unique($ids)));
        }

        $officeId = $this->scope->isAdmin() ? null : (int) $this->scope->officeId();

        foreach ($items as $t) {
            $entries = $entriesByTx->get($t->id) ?? collect();
            $debits = $entries->where('direction', Direction::DEBIT);
            $credits = $entries->where('direction', Direction::CREDIT);

            $t->amount = $debits->sum('amount_minor') / 100;

            $fromEntry = $debits->sortByDesc('amount_minor')->first();
            $toEntry = $credits->sortByDesc('amount_minor')->first();
            $fromAcc = $fromEntry ? $accounts->get($fromEntry->account_id) : null;
            $toAcc = $toEntry ? $accounts->get($toEntry->account_id) : null;

            $t->from_type = $fromAcc->owner_type ?? null;
            $t->from_id = $fromAcc->owner_id ?? null;
            $t->to_type = $toAcc->owner_type ?? null;
            $t->to_id = $toAcc->owner_id ?? null;

            $t->from_label = PartyLabel::label($t->from_type);
            $t->to_label = PartyLabel::label($t->to_type);
            $t->from_name = $this->displayName($names, $t->from_type, $t->from_id);
            $t->to_name = $this->displayName($names, $t->to_type, $t->to_id);

            [$t->description, $t->description_en] = $this->describe($t);
            $t->status = 'completed';

            if ($officeId !== null) {
                $creditedToOffice = $credits->contains(function ($e) use ($accounts, $officeId) {
                    $a = $accounts->get($e->account_id);

                    return $a && $a->owner_type === OwnerType::OFFICE && (int) $a->owner_id === $officeId;
                });
                $t->direction = $creditedToOffice ? 'in' : 'out';
            } else {
                $t->direction = null;
            }
        }
    }

    private function displayName(array $names, ?string $type, $id): string
    {
        if ($type === OwnerType::FLEET) {
            return textByLanguage('المنصّة', 'Platform');
        }
        if ($type === OwnerType::BOOKING) {
            return textByLanguage('حجز', 'Booking') . ' #' . (int) $id;
        }
        if ($type === null || $id === null) {
            return '—';
        }

        return $names[$type][(int) $id] ?? ('#' . (int) $id);
    }

    private function resolveNames(?string $type, array $ids): array
    {
        if (! $type || empty($ids)) {
            return [];
        }

        $conn = $this->connection();

        try {
            return match ($type) {
                OwnerType::OFFICE => Office::on($conn)->whereIn('id', $ids)->pluck('officeName', 'id')->all(),
                OwnerType::DRIVER => Driver::on($conn)->whereIn('id', $ids)->get(['id', 'firstName', 'lastName'])
                    ->mapWithKeys(fn ($d) => [$d->id => trim($d->firstName . ' ' . $d->lastName)])->all(),
                OwnerType::USER => User::query()->whereIn('id', $ids)->get(['id', 'firstName', 'lastName'])
                    ->mapWithKeys(fn ($u) => [$u->id => trim($u->firstName . ' ' . $u->lastName)])->all(),
                default => [],
            };
        } catch (Throwable $e) {
            return [];
        }
    }

    /** A readable, localized description per ledger kind (+ the ride/booking id). */
    private function describe($t): array
    {
        $ref = $t->reference_id ? ' #' . (int) $t->reference_id : '';

        return match ($t->kind) {
            LedgerKind::TOPUP => [textByLanguage('شحن المحفظة', 'Wallet top-up'), 'Wallet top-up'],
            LedgerKind::RIDE_HOLD => [textByLanguage('حجز أجرة الرحلة', 'Ride fare hold') . $ref, 'Ride fare hold' . $ref],
            LedgerKind::RIDE_RELEASE => [textByLanguage('تسوية رحلة', 'Ride settlement') . $ref, 'Ride settlement' . $ref],
            LedgerKind::COMMISSION => [textByLanguage('عمولة رحلة', 'Ride commission') . $ref, 'Ride commission' . $ref],
            LedgerKind::CASH_COMMISSION => [textByLanguage('عمولة رحلة نقدية', 'Cash ride commission') . $ref, 'Cash ride commission' . $ref],
            LedgerKind::DUES_SETTLE => [textByLanguage('تسوية مستحقّات', 'Dues settlement'), 'Dues settlement'],
            LedgerKind::PAYOUT => [textByLanguage('سحب أرباح', 'Payout'), 'Payout'],
            LedgerKind::SUBSCRIPTION => [textByLanguage('اشتراك', 'Subscription'), 'Subscription'],
            LedgerKind::REFUND => [textByLanguage('استرداد', 'Refund') . $ref, 'Refund' . $ref],
            LedgerKind::ADJUSTMENT => [textByLanguage('تسوية يدوية', 'Manual adjustment'), 'Manual adjustment'],
            default => [(string) ($t->description ?? '—'), (string) ($t->description ?? '—')],
        };
    }

    /**
     * Subscription payments — a ledger read-model the legacy screen never showed.
     */
    public function subscriptionPayments(int $limit = 10): array
    {
        $conn = $this->connection();
        $officeId = $this->scope->isAdmin() ? null : (int) $this->scope->officeId();

        try {
            $subscriptions = OfficeSubscription::on($conn)
                ->when($officeId !== null, fn ($q) => $q->where('office_id', $officeId))
                ->get(['id', 'office_id', 'plan_key'])
                ->keyBy('id');

            if ($officeId !== null && $subscriptions->isEmpty()) {
                return [];
            }

            $rows = LedgerTransaction::on($conn)
                ->where('kind', LedgerKind::SUBSCRIPTION)
                ->when($officeId !== null, fn ($q) => $q->whereIn('reference_id', $subscriptions->keys()->all() ?: [0]))
                ->orderByDesc('id')
                ->limit($limit)
                ->get(['id', 'reference_id', 'currency_code', 'description', 'posted_at']);

            if ($rows->isEmpty()) {
                return [];
            }

            $amounts = LedgerEntry::on($conn)
                ->whereIn('transaction_id', $rows->pluck('id')->all())
                ->where('direction', Direction::CREDIT)
                ->get(['transaction_id', 'amount_minor'])
                ->keyBy('transaction_id');

            $officeIds = $subscriptions->pluck('office_id')->unique()->all();
            $names = Office::on($conn)->whereIn('id', $officeIds)->pluck('officeName', 'id');
        } catch (Throwable $e) {
            return [];
        }

        return $rows->map(function ($row) use ($subscriptions, $amounts, $names) {
            $subscription = $subscriptions[$row->reference_id] ?? null;
            $office = $subscription !== null ? ($names[$subscription->office_id] ?? ('#' . $subscription->office_id)) : '—';

            return [
                'id' => (int) $row->id,
                'office' => $office,
                'plan' => $subscription->plan_key ?? '—',
                'amount_minor' => (int) (optional($amounts[$row->id] ?? null)->amount_minor ?? 0),
                'currency' => $row->currency_code,
                'at' => optional($row->posted_at)->format('Y-m-d H:i'),
            ];
        })->all();
    }

    public function summary(): array
    {
        $conn = $this->connection();

        if ($this->scope->isAdmin()) {
            return [
                ['label' => textByLanguage('إجمالي المعاملات', 'Total transactions'), 'icon' => 'bi-arrow-left-right', 'value' => $this->base()->count(), 'money' => false],
                ['label' => textByLanguage('الحجم المكتمل', 'Completed volume'), 'icon' => 'bi-cash-stack', 'value' => $this->scopedDebitTotal() / 100, 'money' => true],
                ['label' => textByLanguage('عمليات الشحن', 'Top-ups'), 'icon' => 'bi-wallet2', 'value' => $this->base()->where('kind', LedgerKind::TOPUP)->count(), 'money' => false],
                ['label' => textByLanguage('التسويات', 'Settlements'), 'icon' => 'bi-check2-circle', 'value' => $this->base()->whereIn('kind', [LedgerKind::RIDE_RELEASE, LedgerKind::COMMISSION, LedgerKind::CASH_COMMISSION])->count(), 'money' => false],
            ];
        }

        $officeId = (int) $this->scope->officeId();
        $received = $this->officeFlow($officeId, Direction::CREDIT) / 100;
        $sent = $this->officeFlow($officeId, Direction::DEBIT) / 100;

        return [
            ['label' => textByLanguage('وارد', 'Received'), 'icon' => 'bi-arrow-down-circle', 'value' => $received, 'money' => true],
            ['label' => textByLanguage('صادر', 'Sent'), 'icon' => 'bi-arrow-up-circle', 'value' => $sent, 'money' => true],
            ['label' => textByLanguage('الصافي', 'Net'), 'icon' => 'bi-wallet2', 'value' => $received - $sent, 'money' => true],
            ['label' => textByLanguage('عدد المعاملات', 'Transactions'), 'icon' => 'bi-list-ul', 'value' => $this->base()->count(), 'money' => false],
        ];
    }

    /** Sum of all debit legs across the in-scope transactions (money moved). */
    private function scopedDebitTotal(): int
    {
        $conn = $this->connection();
        $ids = $this->base()->pluck('id')->all();

        if ($ids === []) {
            return 0;
        }

        return (int) LedgerEntry::on($conn)->whereIn('transaction_id', $ids)->where('direction', Direction::DEBIT)->sum('amount_minor');
    }

    /** Total credited to / debited from an office's accounts (minor units). */
    private function officeFlow(int $officeId, string $direction): int
    {
        $conn = $this->connection();

        return (int) LedgerEntry::on($conn)
            ->where('direction', $direction)
            ->whereIn('account_id', function (QueryBuilder $sub) use ($officeId) {
                $sub->select('id')->from('ledger_accounts')
                    ->where('owner_type', OwnerType::OFFICE)
                    ->where('owner_id', $officeId);
            })
            ->sum('amount_minor');
    }
}
