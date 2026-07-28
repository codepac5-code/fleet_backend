<?php

namespace App\Http\Core\Classes\Auth;

use App\Models\Driver;

interface DriverTokenIssuer
{
    public function issue(Driver $driver, string $name): string;

    public function revokeCurrent(Driver $driver): void;
}
