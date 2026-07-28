<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafetyContact extends Model
{
    protected $connection = 'global';

    protected $table = 'safety_contacts';

    protected $fillable = ['user_id', 'name', 'phone', 'relation', 'is_primary', 'auto_share'];

    protected $casts = [
        'user_id' => 'integer',
        'is_primary' => 'boolean',
        'auto_share' => 'boolean',
    ];
}
