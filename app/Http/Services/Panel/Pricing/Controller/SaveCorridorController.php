<?php

namespace App\Http\Services\Panel\Pricing\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\City;
use App\Models\Office;
use App\Models\SubService;
use App\Models\TravelRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveCorridorController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): RedirectResponse
    {
        $conn = TenantConnection::current();

        $data = $request->validate([
            'sub_service_id' => ['required', 'integer'],
            'departure_city_id' => ['required', 'integer'],
            'arrival_city_id' => ['required', 'integer', 'different:departure_city_id'],
            'trip_price' => ['required', 'numeric', 'min:0'],
            'office_id' => ['nullable', 'integer'],
        ]);

        $officeId = $scope->isAdmin()
            ? (int) ($data['office_id'] ?? 0)
            : (int) $scope->officeId();

        if ($officeId <= 0) {
            return back()->with('error', textByLanguage('المكتب مطلوب.', 'Office is required.'));
        }

        // Every referenced row must exist ON THIS shard — a client cannot point a
        // corridor at another country's city or a non-Travel sub-service.
        $subOk = SubService::on($conn)->whereKey($data['sub_service_id'])->travel()->exists();
        $depOk = City::on($conn)->whereKey($data['departure_city_id'])->exists();
        $arrOk = City::on($conn)->whereKey($data['arrival_city_id'])->exists();
        $officeOk = Office::on($conn)->whereKey($officeId)->exists();

        if (! $subOk || ! $depOk || ! $arrOk || ! $officeOk) {
            return back()->with('error', textByLanguage('بيانات غير صالحة لهذه الدولة.', 'Invalid data for this country.'));
        }

        $route = TravelRoutes::on($conn)->firstOrNew([
            'officeId' => $officeId,
            'sub_service_id' => (int) $data['sub_service_id'],
            'departure_city_id' => (int) $data['departure_city_id'],
            'arrival_city_id' => (int) $data['arrival_city_id'],
        ]);
        $route->setConnection($conn);
        $route->trip_price = (float) $data['trip_price'];
        $route->save();

        return back()->with('status', textByLanguage('تم حفظ سعر الخط.', 'Corridor price saved.'));
    }
}
