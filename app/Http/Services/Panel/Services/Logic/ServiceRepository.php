<?php

namespace App\Http\Services\Panel\Services\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ServiceRepository
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function paginate(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Service::on($this->connection())
            ->withCount('subServices')
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $w) use ($search) {
                    $w->where('title', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Service
    {
        return Service::on($this->connection())->findOrFail($id);
    }

    public function create(array $data): Service
    {
        $service = new Service($data);

        if ($connection = $this->connection()) {
            $service->setConnection($connection);
        }

        $service->save();

        return $service;
    }

    public function update(Service $service, array $data): Service
    {
        $service->fill($data)->save();

        return $service;
    }

    public function toggle(Service $service): Service
    {
        $service->status = $service->status ? 0 : 1;
        $service->save();

        return $service;
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
