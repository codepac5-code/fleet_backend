<?php

namespace App\Http\Core\Repositories\Driver;

use App\Models\Driver;

class EloquentDriverDirectoryRepository implements DriverDirectoryRepository
{
    public function idsForOffice(int $officeId): array
    {
        return Driver::query()
            ->where('officeId', $officeId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
