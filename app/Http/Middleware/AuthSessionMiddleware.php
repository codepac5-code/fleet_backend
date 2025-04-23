<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;


class AuthSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $force = false)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($force || !Session::has('auth_user')) {
                $roles = $user->getRoleNames();

                Session::put('auth_user', [
                    'user' => $user,
                    'roles' => $roles,
                ]);
            }
        }

        return $next($request);
    }

}
