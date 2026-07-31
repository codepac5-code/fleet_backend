<?php

namespace App\Console\Commands;

use App\Http\Core\GeoServices\ShardRunner;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\ShardSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Everything a deployed install needs after pulling new code, in the one order
 * that works: platform migrations first, then every country shard (schema clone
 * + the missing-column sync that a copied migration ledger would otherwise
 * hide), then the idempotent reference seeds, then the caches.
 *
 * Safe to run repeatedly — every step is idempotent.
 */
class UpgradeDeployment extends Command
{
    protected $signature = 'fleet:upgrade
        {--no-seed : skip the idempotent reference seeders}
        {--no-cache : skip rebuilding the config/route/view caches}
        {--dry-run : list the steps without running them}';

    protected $description = 'Bring a deployed install up to date: platform migrations, every shard, reference seeds and caches.';

    public function handle(): int
    {
        $steps = [
            'Platform migrations' => fn () => $this->platformMigrations(),
            'Country shards' => fn () => $this->shards(),
            'Reference seeds' => fn () => $this->seeds(),
            'Caches' => fn () => $this->caches(),
        ];

        if ($this->option('dry-run')) {
            $this->line('Would run:');
            foreach (array_keys($steps) as $name) {
                $this->line('  · ' . $name);
            }

            return self::SUCCESS;
        }

        foreach ($steps as $name => $step) {
            $this->newLine();
            $this->info('▸ ' . $name);

            try {
                $step();
            } catch (Throwable $e) {
                $this->error($name . ' failed: ' . $e->getMessage());

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('Upgrade complete.');
        $this->warn('Still yours to do on the server:');
        $this->line('  · restart php-fpm (opcache with validate_timestamps=0 serves stale code after a cache rebuild)');
        $this->line('  · restart the daemons: fleet-events-relay, fleet-dispatch-tick, fleet-queue, fleet-gateway');

        return self::SUCCESS;
    }

    private function platformMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true], $this->output);
    }

    private function shards(): void
    {
        Artisan::call('fleet:shard-provision', ['--all' => true], $this->output);
    }

    /**
     * The reference data every install needs: platform-wide rows (roles, plans,
     * currencies, colours) plus each shard's own catalogs. Both seeders are
     * firstOrCreate-based, so re-running never duplicates or overwrites.
     */
    private function seeds(): void
    {
        if ($this->option('no-seed')) {
            $this->line('  skipped (--no-seed)');

            return;
        }

        $this->call('db:seed', ['--class' => ProductionSeeder::class, '--force' => true]);

        ShardRunner::eachCountry(function ($node) {
            $previous = DB::getDefaultConnection();
            DB::setDefaultConnection(TenantConnection::NAME);

            try {
                (new ShardSeeder())->run();
                $this->line(sprintf('  seeded %s', $node->country_code ?? $node->id));
            } catch (Throwable $e) {
                $this->warn(sprintf('  %s seeding skipped: %s', $node->country_code ?? $node->id, $e->getMessage()));
            } finally {
                DB::setDefaultConnection($previous);
            }
        });
    }

    private function caches(): void
    {
        if ($this->option('no-cache')) {
            $this->line('  skipped (--no-cache)');

            return;
        }

        foreach (['config:clear', 'route:clear', 'view:clear'] as $command) {
            Artisan::call($command);
            $this->line('  ' . $command);
        }

        // A cached config on a dev box freezes .env over phpunit's own env and
        // makes the test suite fail wholesale, so only a deployed environment
        // gets the caches rebuilt.
        if (app()->environment('local', 'testing')) {
            $this->line('  caches cleared but not rebuilt (local environment)');

            return;
        }

        // route:cache trips over duplicate names in the legacy dashboard routes on
        // some installs, so a failure here is reported, not fatal — the app runs
        // fine uncached, just slower.
        foreach (['config:cache', 'route:cache', 'view:cache'] as $command) {
            try {
                Artisan::call($command);
                $this->line('  ' . $command);
            } catch (Throwable $e) {
                $this->warn('  ' . $command . ' skipped: ' . $e->getMessage());
            }
        }
    }
}
