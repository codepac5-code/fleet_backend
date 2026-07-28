<?php

namespace App\Http\Services\Panel\OfficeBookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\ServiceTariff;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CreateOfficeBookingPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, DriverRepository $drivers, OfficeRepository $offices): View
    {
        $isAdmin = $scope->isAdmin();
        $officeId = $isAdmin ? (int) ($request->query('office') ?: 0) : (int) $scope->officeId();
        $ready = $officeId > 0;

        $node = ShardManager::current();

        return view('panel.office-bookings.create', [
            'entity' => $scope->guard(),
            'isAdmin' => $isAdmin,
            'officeId' => $officeId,
            'ready' => $ready,
            'offices' => $isAdmin ? $offices->options() : [],
            'drivers' => $ready ? $drivers->assignableForOffice($officeId) : [],
            'tariffs' => $ready ? $this->tariffs($officeId) : [],
            'googleMapsKey' => config('services.google_maps.key'),
            'mapCenter' => [
                'lat' => $node && $node->lat ? (float) $node->lat : 24.7136,
                'lng' => $node && $node->lng ? (float) $node->lng : 46.6753,
            ],
        ]);
    }

    private function tariffs(int $officeId): array
    {
        return ServiceTariff::query()
            ->where('office_id', $officeId)
            ->where('is_active', true)
            ->orderBy('service')
            ->get(['service', 'service_class', 'pricing_style', 'currency_code'])
            ->map(fn ($t) => [
                'service' => $t->service ?: 'ride',
                'service_class' => $t->service_class,
                'currency' => $t->currency_code,
            ])
            ->values()
            ->all();
    }
}
