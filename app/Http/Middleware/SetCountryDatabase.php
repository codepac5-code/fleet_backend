<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetCountryDatabase
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        $region = strtolower($user?->current_country ?? 'qa');

        $databaseMap = [
            'qa' => [
                'db' => 'fleet_qa',
                'user' => 'fleet_qa',
                'pass' => 'heWWRXXTYdB66Cez',
            ],
            'us' => [
                'db' => 'fleet_us',
                'user' => 'fleet_us',
                'pass' => 'aFPbfMFAGWPCHPbi',
            ],
            'sy' => [
                'db' => 'fleet_sy',
                'user' => 'fleet_sy',
                'pass' => 'GExSce7Mb7XwAHeB',
            ],
        ];

        $config = $databaseMap[$region] ?? $databaseMap['qa'];

        Config::set('database.connections.country.database', $config['db']);
        Config::set('database.connections.country.username', $config['user']);
        Config::set('database.connections.country.password', $config['pass']);

        DB::purge('country');
        DB::reconnect('country');

        app()->instance('region', $region);

        return $next($request);
    }
}
