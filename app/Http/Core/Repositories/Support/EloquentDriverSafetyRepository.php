<?php

namespace App\Http\Core\Repositories\Support;

use App\Models\DriverSafetyEvent;

class EloquentDriverSafetyRepository implements DriverSafetyRepository
{
    public function create(array $attributes): DriverSafetyEvent
    {
        return DriverSafetyEvent::query()->create($attributes);
    }
}
