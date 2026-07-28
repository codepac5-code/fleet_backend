<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetupBootConfig
{
    public function handle(Request $request, Closure $next)
    {
        config([
            'session.driver' => 'file',
            'cache.default'  => 'file',
            'queue.default'  => 'sync',
        ]);

        return $next($request);
    }
}
