<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\ServiceRepository;
use App\Http\Services\Panel\Services\Logic\SubServiceRepository;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\TravelRoutes;
use Illuminate\Contracts\View\View;
use Throwable;

class SubServicesPageController extends Controller
{
    public function __invoke(int $service, EntityScope $scope, ServiceRepository $services, SubServiceRepository $subServices): View
    {
        $model = $services->findOrFail($service);
        $subs = $subServices->forService($model->id);

        // A TRAVEL service is priced by its fixed city-to-city corridors, never
        // by the meter columns on the sub-service — showing those made an
        // operator believe a per-km price applied when the rider is quoted a
        // flat corridor fare (or gets no offer at all without one).
        $isTravel = (bool) ($model->travel_service ?? false) || $subs->contains(fn ($sub) => (bool) $sub->is_travel);

        return view('panel.services.sub-services.index', [
            'entity'      => $scope->guard(),
            'user'        => $scope->user(),
            'service'     => $model,
            'subServices' => $subs,
            'isTravel'    => $isTravel,
            'currency'    => ShardManager::currency(),
            'corridors'   => $isTravel ? $this->corridorStats($subs->pluck('id')->all()) : [],
        ]);
    }

    /** sub-service id => how many corridors it has and their price range. */
    private function corridorStats(array $subServiceIds): array
    {
        if ($subServiceIds === []) {
            return [];
        }

        try {
            return TravelRoutes::on(TenantConnection::current())
                ->whereIn('sub_service_id', $subServiceIds)
                ->selectRaw('sub_service_id, COUNT(*) AS corridors, MIN(trip_price) AS min_price, MAX(trip_price) AS max_price')
                ->groupBy('sub_service_id')
                ->get()
                ->keyBy('sub_service_id')
                ->map(fn ($row) => [
                    'count' => (int) $row->corridors,
                    'min' => (float) $row->min_price,
                    'max' => (float) $row->max_price,
                ])
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }
}
