<?php

namespace App\Http\Services\Panel\Admin\Cities\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use Illuminate\View\View;

class CitiesPageController extends Controller
{
    public function __invoke(): View
    {
        // Cities live on the active country shard (queried explicitly since City
        // has no tenant trait). Each country manages its own cities — the same
        // list the rider's fixed/cities endpoint reads for that country.
        $cities = City::on(TenantConnection::current())
            ->orderBy('name')
            ->get(['id', 'name', 'name_on_google_map']);

        return view('panel.cities.index', [
            'cities' => $cities,
        ]);
    }
}
