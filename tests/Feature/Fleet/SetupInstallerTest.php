<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Install\Installer;
use Illuminate\Support\Facades\Route;

class SetupInstallerTest extends FleetTestCase
{
    public function test_setup_routes_are_registered(): void
    {
        foreach (['setup.index', 'setup.test', 'setup.database', 'setup.admin', 'setup.country', 'setup.finish'] as $name) {
            $this->assertTrue(Route::has($name), "Missing route: {$name}");
        }
    }

    public function test_lock_file_marks_the_system_installed(): void
    {
        $installer = app(Installer::class);
        $path = $installer->lockPath();
        $existed = is_file($path);
        $backup = $existed ? file_get_contents($path) : null;

        @unlink($path);
        $installer->lock();

        $this->assertTrue(is_file($path));
        $this->assertTrue($installer->isInstalled());

        if ($existed) {
            file_put_contents($path, $backup);
        }
    }
}
