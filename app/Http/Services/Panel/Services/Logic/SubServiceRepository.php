<?php

namespace App\Http\Services\Panel\Services\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\SubService;
use Illuminate\Database\Eloquent\Collection;

class SubServiceRepository
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function forService(int $serviceId): Collection
    {
        return SubService::on($this->connection())
            ->where('serviceId', $serviceId)
            ->orderBy('id')
            ->get();
    }

    public function findForService(int $serviceId, int $id): SubService
    {
        return SubService::on($this->connection())
            ->where('serviceId', $serviceId)
            ->findOrFail($id);
    }

    public function create(array $data): SubService
    {
        $sub = new SubService($data);

        if ($connection = $this->connection()) {
            $sub->setConnection($connection);
        }

        $sub->save();

        return $sub;
    }

    public function update(SubService $sub, array $data): SubService
    {
        $sub->fill($data)->save();

        return $sub;
    }

    public function toggle(SubService $sub): SubService
    {
        $sub->status = $sub->status ? 0 : 1;
        $sub->save();

        return $sub;
    }

    public function delete(SubService $sub): void
    {
        $sub->delete();
    }
}
