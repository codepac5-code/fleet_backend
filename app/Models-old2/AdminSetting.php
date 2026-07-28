<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use AdminSetting;

class AdminSetting extends Model
{
    protected $fillable = ['key','value'];

    protected $casts = [
        'value' => 'array',
    ];
}
