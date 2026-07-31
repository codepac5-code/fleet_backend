<?php

namespace App\Http\Services\Panel\Admin\Offices\Logic;

use App\Http\Services\Panel\Admin\Permissions\Logic\OfficeBaseline;
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
        $serviceIds = $this->pullServiceIds($data);

        $data['password'] = Hash::make($data['password']);

        $office = new Office($data);

        if ($connection = TenantConnection::current()) {
            $office->setConnection($connection);
        }

        $office->save();

        if ($serviceIds !== null) {
            $office->services()->sync($serviceIds);
        }

        // Every path that makes an office — this form, lead approval, the
        // website's self-signup — comes through here, and none of them used to
        // grant it anything. It signed in to a panel of missing screens that
        // answered 403 on save.
        $this->baseline()->grant($office);

        return $office;
    }

    public function update(Office $office, array $data): Office
    {
        $serviceIds = $this->pullServiceIds($data);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $office->fill($data)->save();

        if ($serviceIds !== null) {
            $office->services()->sync($serviceIds);
        }

        return $office;
    }

    /**
     * The services an office runs live in their own table, so they are lifted
     * out of the attribute bag before it reaches `fill()` and written through
     * the relation — one place, whether the office is being created or edited.
     *
     * A payload that never mentions them returns null, not an empty list: an
     * edit screen that does not carry the field must leave the assignment
     * alone rather than quietly strip the office of every service it runs.
     */
    private function baseline(): OfficeBaseline
    {
        return app(OfficeBaseline::class);
    }

    private function pullServiceIds(array &$data): ?array
    {
        if (! array_key_exists('service_ids', $data)) {
            return null;
        }

        $ids = $data['service_ids'];
        unset($data['service_ids']);

        return collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
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
