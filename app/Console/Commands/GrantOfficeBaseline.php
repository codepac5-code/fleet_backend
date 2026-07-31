<?php

namespace App\Console\Commands;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Admin\Permissions\Logic\OfficeBaseline;
use App\Models\InfrastructureNode;
use App\Models\Office;
use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Spatie\Permission\PermissionRegistrar;

/**
 * Gives every EXISTING office the permissions a new one is now created with.
 *
 * Offices were created with nothing granted, so what each can do came down to
 * whether an admin happened to open its permission matrix afterwards: some hold
 * 28 hand-picked names, some hold none at all, and one could open the fixed
 * corridors page but got 403 on save because it had `view sub-service list`
 * without `edit sub-service`.
 *
 * The grant is ADDITIVE on purpose — it never removes a permission an operator
 * deliberately gave or withheld beyond the baseline.
 */
class GrantOfficeBaseline extends Command
{
    protected $signature = 'fleet:office-baseline {--country= : ISO2 or country name, default every shard} {--dry-run}';

    protected $description = 'Grant every office the baseline permissions a new office starts with';

    public function handle(OfficeBaseline $baseline): int
    {
        $dry = (bool) $this->option('dry-run');
        $filter = $this->option('country');

        $nodes = InfrastructureNode::query()
            ->when($filter, fn ($q) => $q->where('name', $filter)->orWhere('iso2', $filter))
            ->get();

        if ($nodes->isEmpty()) {
            $this->error('No country matched.');

            return self::FAILURE;
        }

        foreach ($nodes as $node) {
            ShardManager::activate($node);

            try {
                $names = $baseline->names('dynamic');
            } catch (\Throwable $e) {
                // A shard whose credentials are wrong is the operator's problem,
                // not a reason to leave every later country ungranted.
                $this->warn("[{$node->name}] unreachable — skipped ({$e->getMessage()})");

                continue;
            }

            if ($names === []) {
                $this->warn("[{$node->name}] no permissions on this shard — skipped.");

                continue;
            }

            $permissions = Permission::on('dynamic')
                ->where('guard_name', Guard::$Office)
                ->whereIn('name', $names)
                ->get();

            foreach (Office::on('dynamic')->get() as $office) {
                $held = $office->getAllPermissions()->pluck('name')->all();
                $missing = $permissions->reject(fn ($p) => in_array($p->name, $held, true));

                if ($missing->isEmpty()) {
                    $this->line("  [{$node->name}] #{$office->id} {$office->officeName} — already covered.");

                    continue;
                }

                if (! $dry) {
                    $office->givePermissionTo($missing->all());
                }

                $this->info("  [{$node->name}] #{$office->id} {$office->officeName} — " . ($dry ? 'would add ' : 'added ') . $missing->count() . ': ' . $missing->pluck('name')->implode(', '));
            }
        }

        App::make(PermissionRegistrar::class)->forgetCachedPermissions();

        return self::SUCCESS;
    }
}
