<?php

namespace App\Console\Commands;

use App\Http\Core\Classes\Ledger\LedgerIntegrityService;
use App\Http\Core\GeoServices\ShardRunner;
use Illuminate\Console\Command;

/**
 * Prove the ledger is consistent, per shard. Runs the read-only reconciliation
 * engine ({@see LedgerIntegrityService}) against every active country shard and
 * reports any invariant violation. Exits non-zero the moment ANY shard is
 * inconsistent, so it can gate CI, a cron alarm, or a pre-deploy check.
 *
 * Read-only: it never writes, so it is safe to run against live money at any
 * cadence.
 */
class LedgerVerify extends Command
{
    protected $signature = 'fleet:ledger-verify {--json : Emit the full report as JSON}';

    protected $description = 'Verify ledger integrity (balanced, in-sync, conserved, non-negative) across all shards';

    public function handle(): int
    {
        $reports = [];
        $healthy = true;

        ShardRunner::eachCountry(function ($node) use (&$reports, &$healthy) {
            $shard = $node->name ?? (string) $node->id;
            $report = app(LedgerIntegrityService::class)->verify();
            $reports[$shard] = $report;

            if (! $report['ok']) {
                $healthy = false;
            }

            if (! $this->option('json')) {
                $this->renderShard($shard, $report);
            }
        });

        if ($this->option('json')) {
            $this->line(json_encode(['ok' => $healthy, 'shards' => $reports], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $healthy ? self::SUCCESS : self::FAILURE;
    }

    private function renderShard(string $shard, array $report): void
    {
        $scope = sprintf(
            'accounts=%d transactions=%d entries=%d',
            $report['accounts'],
            $report['transactions'],
            $report['entries'],
        );

        if ($report['ok']) {
            $this->info("[{$shard}] OK — {$scope}");

            return;
        }

        $this->error("[{$shard}] VIOLATIONS: " . count($report['violations']) . " — {$scope}");

        foreach ($report['violations'] as $v) {
            $this->line('  - ' . $v['check'] . ': ' . json_encode(
                array_diff_key($v, ['check' => true]),
                JSON_UNESCAPED_UNICODE,
            ));
        }
    }
}
