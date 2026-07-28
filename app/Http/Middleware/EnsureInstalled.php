<?php

namespace App\Http\Middleware;

use App\Http\Core\Install\Installer;
use Closure;
use Illuminate\Http\Request;

class EnsureInstalled
{
    public function __construct(private Installer $installer)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        if ($this->installer->isInstalled()) {
            if ($request->is('setup', 'setup/*')) {
                return redirect('/');
            }

            return $next($request);
        }

        if ($request->is('setup', 'setup/*') || $request->is('up')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('user/*', 'driver/*', 'realtime/*', 'webhooks/*')) {
            return response()->json([
                'status'     => false,
                'statusCode' => 503,
                'message'    => 'System not installed yet.',
            ], 503);
        }

        return redirect('/setup');
    }
}
