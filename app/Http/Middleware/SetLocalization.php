<?php
namespace App\Http\Middleware;

use App\Http\Core\Const\Options\LanguageOptions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocalization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next)
{
        if ($request->hasHeader('localization') && in_array($request->header('localization'), LanguageOptions::$language)) {
            app()->setLocale($request->header('localization'));
        } else {
            app()->setLocale(session('locale', 'en'));
        }

        return $next($request);
    }
}
