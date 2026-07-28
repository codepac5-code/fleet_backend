<?php

namespace App\Http\Services\Panel\Users\Logic;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    private function query(): Builder
    {
        return User::query();
    }

    public function paginate(?string $search, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('firstName', 'like', "%{$search}%")
                        ->orWhere('lastName', 'like', "%{$search}%")
                        ->orWhere('phoneNumber', 'like', "%{$search}%")
                        ->orWhere('referralCode', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): User
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): User
    {
        $user = new User($data);
        $user->save();

        return $user;
    }

    public function update(User $user, array $data): User
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data)->save();

        return $user;
    }

    public function toggleStatus(User $user, ?string $reason = null): User
    {
        $blocking = (bool) $user->isActive;

        $user->isActive = $blocking ? 0 : 1;
        $user->block_reason = $blocking ? ($reason !== null && $reason !== '' ? $reason : null) : null;
        $user->blocked_at = $blocking ? now() : null;
        $user->save();

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
