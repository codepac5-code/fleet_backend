<?php

namespace App\Http\Core\Repositories\Account;

use App\Models\RiderPaymentMethod;
use Illuminate\Support\Collection;

class EloquentRiderPaymentMethodRepository implements RiderPaymentMethodRepository
{
    public function listForUser(int $userId): Collection
    {
        return RiderPaymentMethod::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }

    public function create(array $attributes): RiderPaymentMethod
    {
        return RiderPaymentMethod::query()->create($attributes);
    }

    public function findForUser(int $id, int $userId): ?RiderPaymentMethod
    {
        return RiderPaymentMethod::query()->where('id', $id)->where('user_id', $userId)->first();
    }

    public function existsForUser(int $userId): bool
    {
        return RiderPaymentMethod::query()->where('user_id', $userId)->exists();
    }

    public function clearDefaults(int $userId): void
    {
        RiderPaymentMethod::query()->where('user_id', $userId)->update(['is_default' => false]);
    }

    public function firstForUser(int $userId): ?RiderPaymentMethod
    {
        return RiderPaymentMethod::query()->where('user_id', $userId)->orderBy('id')->first();
    }

    public function save(RiderPaymentMethod $method): void
    {
        $method->save();
    }

    public function delete(RiderPaymentMethod $method): void
    {
        $method->delete();
    }
}
