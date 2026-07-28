<?php

namespace App\Http\Core\Install;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Installer
{
    public const GLOBAL = 'global';
    private const PROBE = 'install_probe';

    public function lockPath(): string
    {
        return storage_path('app/installed.lock');
    }

    public function isInstalled(): bool
    {
        if (is_file($this->lockPath())) {
            return true;
        }

        try {
            if (Schema::connection(self::GLOBAL)->hasTable('admins')
                && DB::connection(self::GLOBAL)->table('admins')->exists()) {
                $this->lock();

                return true;
            }
        } catch (\Throwable $e) {
        }

        return false;
    }

    public function lock(): void
    {
        @file_put_contents($this->lockPath(), 'installed' . PHP_EOL);
    }

    public function testConnection(array $c): array
    {
        $this->registerProbe($c, true);
        DB::purge(self::PROBE);

        try {
            $version = DB::connection(self::PROBE)->select('select version() as v')[0]->v ?? null;
            DB::purge(self::PROBE);

            return ['ok' => true, 'server' => $version];
        } catch (\Throwable $e) {
            DB::purge(self::PROBE);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function tryCreateDatabase(array $c): array
    {
        $this->registerProbe($c, false);
        DB::purge(self::PROBE);

        try {
            DB::connection(self::PROBE)->statement(
                'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $c['db_name']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
            DB::purge(self::PROBE);

            return ['ok' => true, 'created' => true];
        } catch (\Throwable $e) {
            DB::purge(self::PROBE);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function configureGlobalRuntime(array $c): void
    {
        $prefix = 'database.connections.' . self::GLOBAL;
        config([
            $prefix . '.host'     => $c['db_host'],
            $prefix . '.port'     => $c['db_port'],
            $prefix . '.database' => $c['db_name'],
            $prefix . '.username' => $c['db_user'],
            $prefix . '.password' => $c['db_pass'] ?? '',
        ]);

        DB::purge(self::GLOBAL);
        DB::reconnect(self::GLOBAL);
    }

    public function migrateGlobal(): void
    {
        $conn = DB::connection(self::GLOBAL);
        $conn->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            Artisan::call('migrate', ['--database' => self::GLOBAL, '--force' => true]);
        } finally {
            $conn->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function seedCore(array $admin): void
    {
        putenv('SEED_ADMIN_EMAIL=' . $admin['email']);
        putenv('SEED_ADMIN_PASSWORD=' . $admin['password']);
        putenv('SEED_ADMIN_FIRST_NAME=' . ($admin['firstName'] ?? 'Super'));
        putenv('SEED_ADMIN_LAST_NAME=' . ($admin['lastName'] ?? 'Admin'));

        foreach ([
            \Database\Seeders\Production\RolesAndPermissionsSeeder::class,
            \Database\Seeders\Production\AdminSeeder::class,
            \Database\Seeders\Production\CurrencySeeder::class,
            \Database\Seeders\Production\SubscriptionPlanSeeder::class,
            \Database\Seeders\Production\SiteSettingSeeder::class,
        ] as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--database' => self::GLOBAL, '--force' => true]);
        }
    }

    public function writeEnv(array $pairs): void
    {
        $path = base_path('.env');
        $content = is_file($path) ? file_get_contents($path) : '';

        foreach ($pairs as $key => $val) {
            $line = $key . '=' . $this->escapeEnvValue((string) $val);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content = rtrim($content, "\r\n") . PHP_EOL . $line . PHP_EOL;
            }
        }

        file_put_contents($path, $content);
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/\s|["\'#=]/', $value)) {
            return '"' . str_replace('"', '\"', $value) . '"';
        }

        return $value;
    }

    private function registerProbe(array $c, bool $withDatabase): void
    {
        config(['database.connections.' . self::PROBE => [
            'driver'    => 'mysql',
            'host'      => $c['db_host'],
            'port'      => (int) ($c['db_port'] ?: 3306),
            'database'  => $withDatabase ? $c['db_name'] : '',
            'username'  => $c['db_user'],
            'password'  => $c['db_pass'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options'   => [\PDO::ATTR_TIMEOUT => 5],
        ]]);
    }
}
