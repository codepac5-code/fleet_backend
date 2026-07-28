<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SafetyContact;

class SafetyContact extends Model
{
    protected $connection = 'global';

    protected $table = 'safety_contacts';

    protected $fillable = ['user_id', 'name', 'phone', 'auto_share'];

    protected $casts = [
        'user_id' => 'integer',
        'auto_share' => 'boolean',
    ];
}
