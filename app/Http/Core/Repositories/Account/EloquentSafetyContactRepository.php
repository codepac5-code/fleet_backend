<?php

namespace App\Http\Core\Repositories\Account;

use App\Models\SafetyContact;
use Illuminate\Support\Collection;

class EloquentSafetyContactRepository implements SafetyContactRepository
{
    public function listForUser(int $userId): Collection
    {
        return SafetyContact::query()->where('user_id', $userId)->orderBy('id')->get();
    }

    public function create(array $attributes): SafetyContact
    {
        return SafetyContact::query()->create($attributes);
    }

    public function findForUser(int $id, int $userId): ?SafetyContact
    {
        return SafetyContact::query()->where('id', $id)->where('user_id', $userId)->first();
    }

    public function delete(SafetyContact $contact): void
    {
        $contact->delete();
    }
}
