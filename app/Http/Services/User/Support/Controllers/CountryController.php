<?php

namespace App\Http\Services\User\Support\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Public list of the supported countries (the tenant/shard countries) for the
 * app's login country picker. Sourced from the GLOBAL `countries` table so it
 * needs no shard resolution and can be called before the user is authenticated.
 */
class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = DB::table('countries')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('en_name')
            ->get(['iso2', 'iso3', 'name', 'en_name', 'phone_code', 'currency_code', 'currency_symbol', 'flag']);

        $items = $rows->map(fn ($r) => [
            'iso2' => strtoupper((string) $r->iso2),
            'iso3' => $r->iso3 !== null ? strtoupper((string) $r->iso3) : null,
            'name' => $r->name,
            'en_name' => $r->en_name,
            'dial_code' => $r->phone_code,
            'currency_code' => $r->currency_code,
            'currency_symbol' => $r->currency_symbol,
            'flag' => $r->flag,
        ])->all();

        return Reply::ok(['countries' => $items]);
    }
}
