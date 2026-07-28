<?php

namespace App\Http\Core\Repositories\Support;

use App\Models\DriverSafetyEvent;

interface DriverSafetyRepository
{
    public function create(array $attributes): DriverSafetyEvent;
}
