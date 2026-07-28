<?php

namespace App\Http\Services\Setup\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Install\Installer;
use Illuminate\Contracts\View\View;

class SetupPageController extends Controller
{
    public function __invoke(Installer $installer): View
    {
        $checks = [
            'php'        => version_compare(PHP_VERSION, '8.2.0', '>='),
            'pdo_mysql'  => extension_loaded('pdo_mysql'),
            'env_writable' => is_writable(base_path('.env')) || is_writable(base_path()),
            'storage_writable' => is_writable(storage_path()),
        ];

        return view('setup.index', [
            'checks'   => $checks,
            'appName'  => config('app.name', 'FleetOS'),
            'defaults' => [
                'db_host' => env('DB_HOST', '127.0.0.1'),
                'db_port' => env('DB_PORT', 3306),
                'db_name' => env('DB_DATABASE', 'fleet'),
                'db_user' => env('DB_USERNAME', 'root'),
            ],
        ]);
    }
}
