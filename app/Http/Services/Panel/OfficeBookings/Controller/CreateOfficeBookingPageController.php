<?php

namespace App\Http\Services\Panel\OfficeBookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Catalog\LocalizedName;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\OfficeSubServicePrice;
use App\Models\SubService;
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

    /**
     * The classes this office can book by hand — the ones it actually offers.
     *
     * They were read from `service_tariffs`, a legacy store the office has no
     * screen for any more, so a class it ticked on "My services" never reached
     * this picker while a seeded row it had never heard of did. The pair
     * (`sub_services`, `office_sub_service_prices`) is the source of truth for
     * what an office sells, and a booking's `service_class` is the sub-service
     * name — so read it from there and the picker and the price agree.
     */
    private function tariffs(int $officeId): array
    {
        $offered = OfficeSubServicePrice::query()
            ->where('office_id', $officeId)
            ->where(fn ($q) => $q->where('is_enabled', true)->orWhereNull('is_enabled'))
            ->pluck('sub_service_id')
            ->all();

        if ($offered === []) {
            return [];
        }

        $currency = ShardManager::currency();

        return SubService::query()
            ->whereIn('id', $offered)
            ->where('status', 1)
            ->with('service:id,title,title_en,travel_service')
            ->orderBy('serviceId')
            ->orderBy('id')
            ->get()
            ->map(fn ($sub) => [
                // Travel is booked as a corridor, not by the metre; the chip
                // says so, and the manual booking stores it the same way.
                'service' => ($sub->service?->travel_service) ? 'travel' : 'ride',
                'service_class' => $sub->name_en ?: $sub->name,
                'label' => LocalizedName::of($sub) ?? ('#' . $sub->id),
                'currency' => $currency,
            ])
            ->values()
            ->all();
    }
}
