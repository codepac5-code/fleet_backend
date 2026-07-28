<?php

namespace App\Http\Core\Repositories\Places;

use App\Models\SavedPlace;
use Illuminate\Support\Collection;

interface SavedPlaceRepository
{
    public function listForUser(int $userId): Collection;

    public function create(array $attributes): SavedPlace;

    public function findForUser(int $id, int $userId): ?SavedPlace;

    public function save(SavedPlace $place): void;

    public function delete(SavedPlace $place): void;
}
