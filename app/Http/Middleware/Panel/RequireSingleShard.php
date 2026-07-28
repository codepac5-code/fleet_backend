<?php

namespace App\Http\Middleware\Panel;

use App\Http\Core\GeoServices\ShardAggregator;
use Closure;

/**
 * Blocks per-country WRITE actions while the panel is in "All countries"
 * aggregate mode. In that mode the `dynamic` connection points at cross-DB
 * UNION VIEWS (not insertable), and — more importantly — an action like a
 * broadcast push or a bulk query would silently span every country, breaking
 * isolation. The operator must switch to one specific country first.
 */
class RequireSingleShard
{
    public function handle($request, Closure $next)
    {
        if (ShardAggregator::isActive()) {
            $message = textByLanguage(
                'اختر دولة محدّدة أولاً لتنفيذ هذا الإجراء (وضع «كل الدول» للعرض فقط).',
                'Switch to a specific country first — "All countries" mode is read-only for this action.'
            );

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        return $next($request);
    }
}
