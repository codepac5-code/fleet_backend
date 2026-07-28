<?php

namespace App\Http\Services\Panel\Drivers\Logic;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DriverRepository
{
    public function __construct(private EntityScope $scope) {}

    private function query(): Builder
    {
        $query = Driver::on(TenantConnection::current())->newQuery();

        return $this->scope->scopeByOffice($query);
    }

    public function paginate(?string $search, ?int $officeId = null, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when($officeId, fn (Builder $q) => $q->where('officeId', $officeId))
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('firstName', 'like', "%{$search}%")
                        ->orWhere('lastName', 'like', "%{$search}%")
                        ->orWhere('phoneNumber', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Driver
    {
        return $this->query()->findOrFail($id);
    }

    public function options(): array
    {
        return $this->query()
            ->orderBy('firstName')
            ->get(['id', 'firstName', 'lastName'])
            ->mapWithKeys(fn ($d) => [$d->id => trim($d->firstName . ' ' . $d->lastName)])
            ->all();
    }

    public function assignableForOffice(?int $officeId = null): array
    {
        return $this->buildAssignable($this->query()->when($officeId, fn (Builder $q) => $q->where('officeId', $officeId)));
    }

    public function assignable(): array
    {
        return $this->buildAssignable($this->query());
    }

    private function buildAssignable(Builder $query): array
    {
        return $query
            ->where('isActive', 1)
            ->with('vehicle:id,vehicleBrand,model,plate,color,modelYear,seatsCount')
            ->orderBy('firstName')
            ->get(['id', 'firstName', 'lastName', 'phoneNumber', 'photo', 'vehicleId'])
            ->map(fn ($d) => [
                'id'    => $d->id,
                'name'  => trim($d->firstName . ' ' . $d->lastName) ?: ('#' . $d->id),
                'phone' => $d->phoneNumber,
                'photo' => $d->photo,
                'car'   => $d->vehicle ? [
                    'brand' => $d->vehicle->vehicleBrand,
                    'model' => $d->vehicle->model,
                    'plate' => $d->vehicle->plate,
                    'color' => $d->vehicle->color,
                    'year'  => $d->vehicle->modelYear,
                    'seats' => $d->vehicle->seatsCount,
                ] : null,
            ])
            ->all();
    }

    public function create(array $data): Driver
    {
        $driver = new Driver($data);

        if ($connection = TenantConnection::current()) {
            $driver->setConnection($connection);
        }

        $driver->save();

        return $driver;
    }

    public function update(Driver $driver, array $data): Driver
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $driver->fill($data)->save();

        return $driver;
    }

    public function toggleStatus(Driver $driver): Driver
    {
        $driver->isActive = $driver->isActive ? 0 : 1;
        $driver->save();

        return $driver;
    }

    public function delete(Driver $driver): void
    {
        $driver->delete();
    }
}
