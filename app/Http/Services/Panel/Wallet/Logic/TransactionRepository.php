<?php

namespace App\Http\Services\Panel\Wallet\Logic;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\Office;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TransactionRepository
{
    public function __construct(private EntityScope $scope) {}

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function base(): Builder
    {
        $query = WalletTransaction::on($this->connection())->newQuery();

        if (! $this->scope->isAdmin()) {
            $officeId = $this->scope->officeId();

            $query->where(function (Builder $w) use ($officeId) {
                $w->where(fn (Builder $x) => $x->where('from_type', Office::class)->where('from_id', $officeId))
                    ->orWhere(fn (Builder $x) => $x->where('to_type', Office::class)->where('to_id', $officeId));
            });
        }

        return $query;
    }

    public function paginate(?string $search, ?string $status, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->base()
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $w) use ($search) {
                    $w->where('transaction_reference', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('description_en', 'like', "%{$search}%")
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
        return $this->base()
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'transaction_reference', 'amount', 'status', 'from_type', 'from_id', 'to_type', 'to_id', 'created_at'])
            ->map(fn ($t) => [
                $t->id,
                $t->transaction_reference,
                $t->amount,
                $t->status,
                class_basename((string) $t->from_type) . ':' . $t->from_id,
                class_basename((string) $t->to_type) . ':' . $t->to_id,
                optional($t->created_at)->format('Y-m-d H:i'),
            ])
            ->all();
    }

    private function enrich(Collection $items): void
    {
        $byType = [];
        foreach ($items as $t) {
            $byType[$t->from_type][] = $t->from_id;
            $byType[$t->to_type][] = $t->to_id;
        }

        $names = [];
        foreach ($byType as $type => $ids) {
            $names[$type] = $this->resolveNames($type, array_values(array_unique(array_filter($ids))));
        }

        $officeId = $this->scope->isAdmin() ? null : $this->scope->officeId();

        foreach ($items as $t) {
            $t->from_label = PartyLabel::label($t->from_type);
            $t->to_label = PartyLabel::label($t->to_type);
            $t->from_name = $names[$t->from_type][$t->from_id] ?? ('#' . $t->from_id);
            $t->to_name = $names[$t->to_type][$t->to_id] ?? ('#' . $t->to_id);

            if ($officeId !== null) {
                $t->direction = ($t->from_type === Office::class && (int) $t->from_id === (int) $officeId) ? 'out' : 'in';
            } else {
                $t->direction = null;
            }
        }
    }

    private function resolveNames(?string $type, array $ids): array
    {
        if (! $type || empty($ids)) {
            return [];
        }

        $conn = $this->connection();

        return match ($type) {
            Office::class => Office::on($conn)->whereIn('id', $ids)->pluck('officeName', 'id')->all(),
            Driver::class => Driver::on($conn)->whereIn('id', $ids)->get(['id', 'firstName', 'lastName'])
                ->mapWithKeys(fn ($d) => [$d->id => trim($d->firstName . ' ' . $d->lastName)])->all(),
            User::class => User::query()->whereIn('id', $ids)->get(['id', 'firstName', 'lastName'])
                ->mapWithKeys(fn ($u) => [$u->id => trim($u->firstName . ' ' . $u->lastName)])->all(),
            default => [],
        };
    }

    public function summary(): array
    {
        $conn = $this->connection();

        if ($this->scope->isAdmin()) {
            return [
                ['label' => textByLanguage('إجمالي المعاملات', 'Total transactions'), 'icon' => 'bi-arrow-left-right', 'value' => $this->base()->count(), 'money' => false],
                ['label' => textByLanguage('الحجم المكتمل', 'Completed volume'), 'icon' => 'bi-cash-stack', 'value' => $this->base()->where('status', 'completed')->sum('amount'), 'money' => true],
                ['label' => textByLanguage('معلّقة', 'Pending'), 'icon' => 'bi-hourglass-split', 'value' => $this->base()->where('status', 'pending')->count(), 'money' => false],
                ['label' => textByLanguage('فاشلة', 'Failed'), 'icon' => 'bi-x-circle', 'value' => $this->base()->where('status', 'failed')->count(), 'money' => false],
            ];
        }

        $officeId = $this->scope->officeId();
        $received = WalletTransaction::on($conn)->where('to_type', Office::class)->where('to_id', $officeId)->sum('amount');
        $sent = WalletTransaction::on($conn)->where('from_type', Office::class)->where('from_id', $officeId)->sum('amount');

        return [
            ['label' => textByLanguage('وارد', 'Received'), 'icon' => 'bi-arrow-down-circle', 'value' => $received, 'money' => true],
            ['label' => textByLanguage('صادر', 'Sent'), 'icon' => 'bi-arrow-up-circle', 'value' => $sent, 'money' => true],
            ['label' => textByLanguage('الصافي', 'Net'), 'icon' => 'bi-wallet2', 'value' => $received - $sent, 'money' => true],
            ['label' => textByLanguage('عدد المعاملات', 'Transactions'), 'icon' => 'bi-list-ul', 'value' => $this->base()->count(), 'money' => false],
        ];
    }
}
