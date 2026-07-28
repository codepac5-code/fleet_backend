<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Close a billing period's accrued plan-overage into per-office invoices, across
 * every country shard. Pending overage charges are stamped invoiced under one
 * reference per office so the fee is collectable and never double-billed.
 * Idempotent: re-running a closed period bills nothing. Defaults to last month.
 */
class OverageClose extends Command
{
    protected $signature = 'fleet:overage-close {--period= : Billing period YYYY-MM (default: last month)}';

    protected $description = 'Close accrued plan-overage into per-office invoices across all shards';

    public function handle(): int
    {
        $period = (string) ($this->option('period') ?: Carbon::now()->subMonthNoOverflow()->format('Y-m'));

        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            $this->error("Invalid period '{$period}' — expected YYYY-MM.");

            return self::FAILURE;
        }

        $grandTotal = 0;
        $grandCount = 0;

        ShardRunner::eachCountry(function ($node) use ($period, &$grandTotal, &$grandCount) {
            $shard = $node->name ?? (string) $node->id;
            $invoices = app(PlanOverageService::class)->closePeriod($period);

            if ($invoices === []) {
                $this->line("[{$shard}] {$period}: nothing to bill");

                return;
            }

            foreach ($invoices as $invoice) {
                $grandTotal += $invoice['total_minor'];
                $grandCount += $invoice['count'];
                $this->info(sprintf(
                    '[%s] %s office#%d → %.2f %s (%d items)',
                    $shard,
                    $invoice['invoice_ref'],
                    $invoice['office_id'],
                    $invoice['total_minor'] / 100,
                    $invoice['currency'],
                    $invoice['count'],
                ));
            }
        });

        $this->line(sprintf('Done — %d items invoiced, %.2f (minor: %d) total.', $grandCount, $grandTotal / 100, $grandTotal));

        return self::SUCCESS;
    }
}
