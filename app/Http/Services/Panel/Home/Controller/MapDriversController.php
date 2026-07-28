<?php

namespace App\Http\Services\Panel\Home\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;

class MapDriversController extends Controller
{
    public function __invoke(EntityScope $scope): JsonResponse
    {
        $drivers = RedisManagerData::getOnlineDriversMapLocations();

        if (! $scope->isAdmin()) {
            $ids = Driver::on(TenantConnection::current())
                ->where('officeId', $scope->officeId())
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $drivers = array_values(array_filter(
                $drivers,
                fn ($d) => in_array((string) $d['driver_id'], $ids, true)
            ));
        }

        return response()->json($drivers);
    }
}
