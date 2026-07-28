<?php

namespace App\Http\Services\Panel\Vehicles\Logic;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class VehicleRepository
{
    public function __construct(private EntityScope $scope) {}

    private function query(): Builder
    {
        $query = Vehicle::on(TenantConnection::current())->newQuery();

        return $this->scope->scopeByOffice($query);
    }

    public function paginate(?string $search, ?int $officeId = null, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when($officeId, fn (Builder $q) => $q->where('officeId', $officeId))
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('vehicleBrand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('plate', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Vehicle
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        $vehicle = new Vehicle($data);

        if ($connection = TenantConnection::current()) {
            $vehicle->setConnection($connection);
        }

        $vehicle->save();

        return $vehicle;
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->fill($data)->save();

        return $vehicle;
    }

    public function delete(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }
}
