<?php

namespace App\Http\Services\Panel\Pricing\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Catalog\LocalizedName;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\City;
use App\Models\Office;
use App\Models\SubService;
use App\Models\TravelRoutes;
use Illuminate\Contracts\View\View;

/**
 * Fixed-corridor (Travel) pricing — the new-panel home for `travel_routes`,
 * the flat departure-city → arrival-city price an office charges for a Travel
 * sub-service. This is the exact table {@see \App\Http\Core\Classes\Ride\FixedTripService}
 * reads to build fixed-trip offers, so what an office publishes here is what the
 * rider is offered. Previously this only existed on the legacy "my-services"
 * dashboard; the new panel had cities and meter tariffs but no corridor screen.
 *
 * Everything is per-country: travel_routes / cities / sub_services live on the
 * active shard and have no tenant trait, so they are queried EXPLICITLY on the
 * tenant connection (same pattern as the cities screen).
 */
class CorridorsPageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        $conn = TenantConnection::current();

        // Same definition the save guard uses — see SubService::scopeTravel.
        $subServices = SubService::on($conn)
            ->where('status', 1)
            ->travel()
            ->orderBy('name')
            ->get(['id', 'name', 'name_en']);

        $cities = City::on($conn)
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = TravelRoutes::on($conn)
            ->with(['subService', 'departureCity', 'arrivalCity', 'office'])
            ->orderByDesc('id');

        // Office/employee see only their own corridors; admin sees every office.
        $scope->scopeByOffice($query, 'officeId');

        // The ids travel with the row so the list can load a corridor straight
        // back into the form — saving the same triple updates it in place.
        $routes = $query->get()->map(fn (TravelRoutes $r) => [
            'id' => $r->id,
            'sub_service' => LocalizedName::of($r->subService) ?? '#' . $r->sub_service_id,
            'sub_service_id' => $r->sub_service_id,
            'departure' => LocalizedName::of($r->departureCity) ?? '#' . $r->departure_city_id,
            'departure_city_id' => $r->departure_city_id,
            'arrival' => LocalizedName::of($r->arrivalCity) ?? '#' . $r->arrival_city_id,
            'arrival_city_id' => $r->arrival_city_id,
            'office' => $r->office?->displayName ?? $r->office?->officeName,
            'office_id' => $r->officeId,
            'trip_price' => (float) $r->trip_price,
        ])->all();

        // Admin can attach a corridor to any office on the shard; office/employee
        // are locked to their own.
        $offices = $scope->isAdmin()
            ? Office::on($conn)->orderBy('id')->get(['id', 'officeName', 'displayName'])
            : collect();

        return view('panel.pricing.corridors', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'routes' => $routes,
            'subServices' => $subServices,
            'cities' => $cities,
            'offices' => $offices,
            'currency' => ShardManager::currency(),
        ]);
    }
}
