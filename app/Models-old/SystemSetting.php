<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SystemSetting;

class SystemSetting extends Model
{
    protected $connection = 'global';
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];
}
