<?php

namespace App\Http\Core\Repositories\Account;

use App\Models\RiderProfile;

class EloquentRiderProfileRepository implements RiderProfileRepository
{
    public function forUser(int $userId): RiderProfile
    {
        return RiderProfile::query()->firstOrNew(['user_id' => $userId]);
    }

    public function save(RiderProfile $profile): void
    {
        $profile->save();
    }
}
