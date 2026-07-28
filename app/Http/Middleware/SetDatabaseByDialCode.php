<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetDatabaseByDialCode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $dialCode = auth()->user()->dialCode;
            $connections = [
                '+963' => 'mysql_sy',
                '+1'   => 'mysql_us',
                '+974' => 'mysql_qa',
                '+971' => 'mysql_ae',
            ];

            $connection = $connections[$dialCode] ?? 'mysql';

            Config::set('database.default', $connection);
            DB::purge($connection);
            DB::reconnect($connection);
        }

        return $next($request);
    }
}
