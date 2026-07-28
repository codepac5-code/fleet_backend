<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserApiEnvelope
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'en';

        if ($request->hasHeader('Accept-Language')) {
            $header = strtolower(substr((string) $request->header('Accept-Language'), 0, 2));
            $locale = in_array($header, ['en', 'ar'], true) ? $header : $locale;
        }

        app()->setLocale($locale);

        $request->attributes->set('fleet_v2', true);

        return $next($request);
    }
}
