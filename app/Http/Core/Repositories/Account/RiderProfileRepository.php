<?php

namespace App\Http\Core\Repositories\Account;

use App\Models\RiderProfile;

interface RiderProfileRepository
{
    public function forUser(int $userId): RiderProfile;

    public function save(RiderProfile $profile): void;
}
