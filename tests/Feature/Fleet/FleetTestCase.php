<?php

namespace Tests\Feature\Fleet;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class FleetTestCase extends TestCase
{
    protected array $globalMigrations = [];
    protected array $tenantMigrations = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.whatsapp.token' => null, 'services.whatsapp.session_id' => null, 'services.smsala.username' => null]);

        $mem = fn () => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true];

        config([
            'database.default' => 'fleet_test',
            'database.connections.fleet_test' => $mem(),
            'database.connections.global' => $mem(),
        ]);

        DB::purge('fleet_test');
        DB::purge('global');

        DB::setDefaultConnection('global');
        foreach ($this->globalMigrations as $file) {
            $this->runMigration($file);
        }

        DB::setDefaultConnection('fleet_test');
        foreach ($this->tenantMigrations as $file) {
            $this->runMigration($file);
        }
    }

    protected function runMigration(string $file): void
    {
        $path = database_path('migrations/' . $file);

        // Modern migrations `return new class extends Migration {...}` and can be
        // required per test. Older ones declare a NAMED class, and requiring such
        // a file twice in one PHP process is a fatal redeclare — so load those
        // once and instantiate by name on later tests.
        $class = $this->namedMigrationClass($path);

        if ($class !== null) {
            if (! class_exists($class, false)) {
                require_once $path;
            }

            (new $class())->up();

            return;
        }

        (require $path)->up();
    }

    /** The declared class name for a legacy migration, or null if anonymous. */
    private function namedMigrationClass(string $path): ?string
    {
        $source = @file_get_contents($path);

        if ($source === false) {
            return null;
        }

        return preg_match('/^\s*class\s+(\w+)/m', $source, $m) === 1 ? $m[1] : null;
    }
}
