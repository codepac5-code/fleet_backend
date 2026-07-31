<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffTwoFactor extends Model
{
    protected $connection = 'global';

    protected $table = 'staff_two_factor';

    protected $fillable = [
        'guard', 'staff_id', 'country_code', 'secret', 'recovery_codes', 'confirmed_at', 'last_used_at',
    ];

    protected $casts = [
        'staff_id' => 'integer',
        'confirmed_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
