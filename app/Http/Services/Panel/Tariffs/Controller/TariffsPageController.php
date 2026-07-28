<?php

namespace App\Http\Services\Panel\Tariffs\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Pricing\TariffService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class TariffsPageController extends Controller
{
    public function __invoke(EntityScope $scope, TariffService $tariffs): View
    {
        $rows = array_map(fn ($t) => [
            'service_class'    => $t->service_class,
            'currency_code'    => $t->currency_code,
            'pricing_style'    => $t->pricing_style,
            'base_minor'       => (int) $t->base_minor,
            'per_km_minor'     => (int) $t->per_km_minor,
            'per_minute_minor' => (int) $t->per_minute_minor,
            'minimum_minor'    => (int) $t->minimum_minor,
            'fixed_minor'      => (int) $t->fixed_minor,
            'is_active'        => (bool) $t->is_active,
        ], $tariffs->forOffice((int) $scope->officeId()));

        return view('panel.tariffs.index', [
            'entity'         => $scope->guard(),
            'tariffs'        => $rows,
            'currency'       => ShardManager::currency(),
            'serviceClasses' => ['standard', 'comfort', 'electric', 'suv', 'van', 'luxury'],
        ]);
    }
}
