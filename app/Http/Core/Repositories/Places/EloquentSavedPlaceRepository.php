<?php

namespace App\Http\Core\Repositories\Places;

use App\Models\SavedPlace;
use Illuminate\Support\Collection;

class EloquentSavedPlaceRepository implements SavedPlaceRepository
{
    public function listForUser(int $userId): Collection
    {
        return SavedPlace::query()->where('user_id', $userId)->orderBy('id')->get();
    }

    public function create(array $attributes): SavedPlace
    {
        return SavedPlace::query()->create($attributes);
    }

    public function findForUser(int $id, int $userId): ?SavedPlace
    {
        return SavedPlace::query()->where('id', $id)->where('user_id', $userId)->first();
    }

    public function save(SavedPlace $place): void
    {
        $place->save();
    }

    public function delete(SavedPlace $place): void
    {
        $place->delete();
    }
}
