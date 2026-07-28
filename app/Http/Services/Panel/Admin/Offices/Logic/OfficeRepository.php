<?php

namespace App\Http\Services\Panel\Admin\Offices\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class OfficeRepository
{
    private function query(): Builder
    {
        return Office::on(TenantConnection::current())->newQuery();
    }

    public function paginate(?string $search, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('officeName', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contactNumber', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Office
    {
        return $this->query()->find($id);
    }

    public function options(): array
    {
        return $this->query()
            ->orderBy('officeName')
            ->pluck('officeName', 'id')
            ->all();
    }

    public function findOrFail(int $id): Office
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Office
    {
        $data['password'] = Hash::make($data['password']);

        $office = new Office($data);

        if ($connection = TenantConnection::current()) {
            $office->setConnection($connection);
        }

        $office->save();

        return $office;
    }

    public function update(Office $office, array $data): Office
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $office->fill($data)->save();

        return $office;
    }

    public function toggleStatus(Office $office): Office
    {
        $office->status = $office->status ? 0 : 1;
        $office->save();

        return $office;
    }

    public function delete(Office $office): void
    {
        $office->delete();
    }
}
