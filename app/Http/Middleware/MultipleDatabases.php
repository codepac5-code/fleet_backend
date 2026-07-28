<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\SystemSetting;

class MultipleDatabases
{
    public function handle(Request $request, Closure $next): Response
    {
        $region = Session::get('region');

        if (!$region) {
            $countryData = json_decode(
                SystemSetting::on('global')
                    ->where('key', 'country')
                    ->value('value'),
                true
            );

            $region = $countryData['iso2'] ?? 'qa';

            Session::put('region', $region);
        }

        $databaseMap = [
            'sy' => 'fleet_sy',
            'us' => 'fleet_us',
            'qa' => 'fleet_qa',
        ];

        $database = $databaseMap[$region] ?? 'fleet_qa';

        Config::set('database.connections.country.database', $database);

        app()->instance('region', $region);

        $dialCodes = [
            'sy' => '+963',
            'us' => '+1',
            'qa' => '+974',
        ];

        if (isset($dialCodes[$region])) {
            Session::put('dialCode', $dialCodes[$region]);
        }

        return $next($request);
    }
}
