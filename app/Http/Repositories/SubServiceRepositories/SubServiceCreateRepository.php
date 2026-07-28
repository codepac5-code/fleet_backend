<?php
namespace app\Http\Repositories\SubServiceRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\SubService;
use App\Models\TravelRoutes;

class SubServiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new SubService();

    }


    public function createWithRoutes(array $data, ?array $routes = null)
 {
        $sub_service = $this->model->create($data);

        TravelRoutes::where('sub_service_id', $sub_service->id)->delete();

        if ($routes !== null) {

            $formatted = collect($routes)->map(function ($r) use ($sub_service) {
                return [
                    'sub_service_id' => $sub_service->id,
                    'departure_city' => $r['departureCity'],
                    'arrival_city'   => $r['arrivalCity'],
                    'trip_price'     => $r['tripPrice'],
                ];
            })->toArray();

            TravelRoutes::insert($formatted);
        }

        return $sub_service;
    }

}
