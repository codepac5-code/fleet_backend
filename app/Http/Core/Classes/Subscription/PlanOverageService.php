<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Subscription\Billing\OverageBillingGateway;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\OfficeSubscription;
use App\Models\PlanOverageCharge;
use Carbon\Carbon;
use Throwable;

/**
 * Accrues plan-overage fees to the office's invoice (never an immediate debit).
 * Idempotent per (type, reference) via the table's unique key, so the same ride
 * or driver is only ever charged once. Everything is scoped to the active
 * country shard through PlanUsageService + PlanOverageCharge's tenant trait.
 */
class PlanOverageService
{
    public const TYPE_RIDE = 'ride';
    public const TYPE_DRIVER = 'driver';

    public function __construct(
        private PlanUsageService $usage,
        private ?OverageBillingGateway $gateway = null,
        private ?EventBus $events = null
    ) {
    }

    /**
     * Charge a ride that pushes the office past its monthly ride limit, when the
     * plan permits paid overage. Call this AT SETTLEMENT — before the ride is
     * marked completed — so `rides_used` is the count BEFORE it, and this ride's
     * position is `rides_used + 1`. No-op off-plan / unlimited / within limit.
     */
    public function recordRideOverage(int $officeId, int $bookingId): ?PlanOverageCharge
    {
        $usage = $this->usage->forOffice($officeId);

        if ($usage === null || $usage['ride_limit'] === null || $usage['extra_ride_minor'] === null) {
            return null;
        }

        if (($usage['rides_used'] + 1) <= $usage['ride_limit']) {
            return null;
        }

        return $this->accrue($officeId, self::TYPE_RIDE, 'booking', $bookingId, (int) $usage['extra_ride_minor'], (string) $usage['currency']);
    }

    /** Charge one extra driver added beyond the plan's driver limit. */
    public function recordDriverOverage(int $officeId, int $driverId, int $amountMinor, string $currency): ?PlanOverageCharge
    {
        if ($amountMinor <= 0) {
            return null;
        }

        return $this->accrue($officeId, self::TYPE_DRIVER, 'driver', $driverId, $amountMinor, $currency);
    }

    /** Sum of not-yet-invoiced overage for an office in a billing period. */
    public function pendingTotalMinor(int $officeId, ?string $period = null): int
    {
        $period = $period ?? Carbon::now()->format('Y-m');

        try {
            return (int) PlanOverageCharge::on(TenantConnection::current())
                ->where('office_id', $officeId)
                ->where('period', $period)
                ->where('status', 'pending')
                ->sum('amount_minor');
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** Pending overage totals keyed by office_id, in one grouped query. */
    public function pendingByOffice(?string $period = null): array
    {
        $period = $period ?? Carbon::now()->format('Y-m');

        try {
            return PlanOverageCharge::on(TenantConnection::current())
                ->where('period', $period)
                ->where('status', 'pending')
                ->groupBy('office_id')
                ->selectRaw('office_id, SUM(amount_minor) AS total')
                ->pluck('total', 'office_id')
                ->map(fn ($v) => (int) $v)
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Close an office's overage for a period: stamp every still-pending charge as
     * invoiced under one reference so it is collectable and can never be double-
     * billed. Idempotent — a second run finds nothing pending and returns null.
     * Returns the closed invoice summary, or null when there was nothing to bill.
     */
    public function closeOffice(int $officeId, ?string $period = null): ?array
    {
        $period = $period ?? Carbon::now()->format('Y-m');
        $conn = TenantConnection::current();

        $pending = PlanOverageCharge::on($conn)
            ->where('office_id', $officeId)
            ->where('period', $period)
            ->where('status', 'pending');

        $totalMinor = (int) (clone $pending)->sum('amount_minor');
        $count = (clone $pending)->count();

        if ($count === 0) {
            return null;
        }

        $invoiceRef = sprintf('OVR-%s-%d', $period, $officeId);
        $currency = (string) ((clone $pending)->value('currency_code') ?? '');

        (clone $pending)->update([
            'status' => 'invoiced',
            'invoice_ref' => $invoiceRef,
            'invoiced_at' => Carbon::now(),
        ]);

        $invoice = [
            'office_id' => $officeId,
            'period' => $period,
            'invoice_ref' => $invoiceRef,
            'total_minor' => $totalMinor,
            'currency' => $currency,
            'count' => $count,
        ];

        $invoice = array_merge($invoice, $this->handOff($invoice, $conn));
        $this->announce($invoice);

        return $invoice;
    }

    /**
     * Tell the office (and fleet admins) an overage invoice was raised — additive
     * office/admin channel event, so the panel surfaces it live and a durable
     * notification is created. Best-effort; never breaks a closed invoice.
     */
    private function announce(array $invoice): void
    {
        if ($this->events === null) {
            return;
        }

        try {
            $this->events->emit(new DomainEvent(
                EventType::OVERAGE_INVOICED,
                [Channel::office((int) $invoice['office_id']), Channel::admin()],
                [
                    'office_id' => (int) $invoice['office_id'],
                    'invoice_ref' => (string) $invoice['invoice_ref'],
                    'period' => (string) $invoice['period'],
                    'total_minor' => (int) $invoice['total_minor'],
                    'currency' => (string) $invoice['currency'],
                    'collection_method' => (string) ($invoice['collection_method'] ?? 'manual'),
                ]
            ));
        } catch (Throwable $e) {
        }
    }

    /**
     * Hand a freshly closed invoice to the billing gateway (best-effort) and
     * stamp how it will be collected. A provider push records collection_method
     * + external_ref on the invoice's rows; a manual fallback leaves it for staff
     * confirmation. Never throws — the invoice is already closed regardless.
     */
    private function handOff(array $invoice, ?string $conn): array
    {
        $result = ['collection_method' => 'manual', 'external_ref' => null];

        if ($this->gateway === null) {
            return $result;
        }

        try {
            $subscription = OfficeSubscription::on($conn)
                ->where('office_id', $invoice['office_id'])
                ->orderByDesc('id')
                ->first();

            $outcome = $this->gateway->bill(
                $invoice,
                $subscription->provider_customer_id ?? null,
                $subscription->provider_subscription_id ?? null,
            );

            $result['collection_method'] = (string) ($outcome['method'] ?? 'manual');
            $result['external_ref'] = $outcome['external_ref'] ?? null;

            PlanOverageCharge::on($conn)
                ->where('invoice_ref', $invoice['invoice_ref'])
                ->update([
                    'collection_method' => $result['collection_method'],
                    'external_ref' => $result['external_ref'],
                ]);
        } catch (Throwable $e) {
        }

        return $result;
    }

    /**
     * Close every fully-elapsed period (strictly before the current calendar
     * month) that still has pending overage for an office — one invoice per
     * period. The in-progress month keeps accruing. Called at each renewal so
     * collection tracks the billing cycle. Idempotent + best-effort (never
     * throws — a missing table on an unprovisioned shard yields []).
     */
    public function closeElapsedForOffice(int $officeId): array
    {
        $current = Carbon::now()->format('Y-m');

        try {
            $periods = PlanOverageCharge::on(TenantConnection::current())
                ->where('office_id', $officeId)
                ->where('status', 'pending')
                ->where('period', '<', $current)
                ->distinct()
                ->pluck('period')
                ->all();
        } catch (Throwable $e) {
            return [];
        }

        $invoices = [];

        foreach ($periods as $period) {
            $invoice = $this->closeOffice($officeId, (string) $period);

            if ($invoice !== null) {
                $invoices[] = $invoice;
            }
        }

        return $invoices;
    }

    /**
     * Mark an office's STRIPE-pushed overage as collected.
     *
     * Stripe-billed items are attached to the office's UPCOMING subscription
     * invoice, so the money arrives with the next `invoice.paid` — at which
     * point every invoice already handed off to Stripe before that payment has
     * been paid. Manual invoices are untouched: those wait for a human to
     * confirm the transfer. Call this BEFORE closing the newly elapsed period,
     * or the just-closed invoice (whose items go on the NEXT Stripe invoice)
     * would be marked paid a cycle early.
     */
    public function markStripeCollectedForOffice(int $officeId): int
    {
        try {
            return PlanOverageCharge::on(TenantConnection::current())
                ->where('office_id', $officeId)
                ->where('status', 'invoiced')
                ->where('collection_method', 'stripe')
                ->update([
                    'status' => 'collected',
                    'collected_at' => Carbon::now(),
                ]);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** Mark a closed overage invoice as collected once its money has been received. */
    public function markCollected(string $invoiceRef): int
    {
        try {
            return PlanOverageCharge::on(TenantConnection::current())
                ->where('invoice_ref', $invoiceRef)
                ->where('status', 'invoiced')
                ->update([
                    'status' => 'collected',
                    'collected_at' => Carbon::now(),
                ]);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Closed overage invoices for the active shard, newest first, grouped by ref.
     * `status` filters to 'invoiced' (awaiting collection) or 'collected'.
     */
    public function invoices(?string $status = null): array
    {
        try {
            $query = PlanOverageCharge::on(TenantConnection::current())
                ->whereNotNull('invoice_ref');

            if ($status !== null) {
                $query->where('status', $status);
            }

            return $query
                ->selectRaw('invoice_ref, office_id, period, status, currency_code, collection_method, external_ref, MAX(invoiced_at) AS invoiced_at, MAX(collected_at) AS collected_at, SUM(amount_minor) AS total_minor, COUNT(*) AS items')
                ->groupBy('invoice_ref', 'office_id', 'period', 'status', 'currency_code', 'collection_method', 'external_ref')
                ->orderByDesc('invoiced_at')
                ->get()
                ->map(fn ($r) => [
                    'invoice_ref' => (string) $r->invoice_ref,
                    'office_id' => (int) $r->office_id,
                    'period' => (string) $r->period,
                    'status' => (string) $r->status,
                    'currency' => (string) ($r->currency_code ?? ''),
                    'collection_method' => (string) ($r->collection_method ?? 'manual'),
                    'external_ref' => $r->external_ref,
                    'total_minor' => (int) $r->total_minor,
                    'items' => (int) $r->items,
                    'invoiced_at' => $r->invoiced_at,
                    'collected_at' => $r->collected_at,
                ])
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Flat rows for a CSV export of closed overage invoices (active shard). */
    public function exportRows(?string $status = null): array
    {
        $date = fn ($v) => $v === null ? '' : substr((string) $v, 0, 10);

        return array_map(fn ($i) => [
            $i['invoice_ref'],
            $i['office_id'],
            $i['period'],
            number_format($i['total_minor'] / 100, 2, '.', ''),
            $i['currency'],
            $i['items'],
            $i['collection_method'],
            $i['external_ref'],
            $i['status'],
            $date($i['invoiced_at']),
            $date($i['collected_at']),
        ], $this->invoices($status));
    }

    /**
     * Close every office that has pending overage in a period (one invoice each).
     * Scoped to the active shard. Returns the per-office invoice summaries.
     */
    public function closePeriod(?string $period = null): array
    {
        $period = $period ?? Carbon::now()->format('Y-m');

        $officeIds = PlanOverageCharge::on(TenantConnection::current())
            ->where('period', $period)
            ->where('status', 'pending')
            ->distinct()
            ->pluck('office_id')
            ->all();

        $invoices = [];

        foreach ($officeIds as $officeId) {
            $invoice = $this->closeOffice((int) $officeId, $period);

            if ($invoice !== null) {
                $invoices[] = $invoice;
            }
        }

        return $invoices;
    }

    private function accrue(int $officeId, string $type, string $refType, int $refId, int $amountMinor, string $currency): ?PlanOverageCharge
    {
        $charge = new PlanOverageCharge([
            'office_id' => $officeId,
            'period' => Carbon::now()->format('Y-m'),
            'type' => $type,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'amount_minor' => $amountMinor,
            'currency_code' => $currency,
            'status' => 'pending',
            'created_at' => Carbon::now(),
        ]);

        if ($conn = TenantConnection::current()) {
            $charge->setConnection($conn);
        }

        try {
            $charge->save();

            return $charge;
        } catch (Throwable $e) {
            // Unique-key violation = already accrued for this reference → idempotent.
            return null;
        }
    }
}
