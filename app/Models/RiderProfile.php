<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderProfile extends Model
{
    protected $connection = 'global';

    protected $table = 'rider_profiles';

    protected $fillable = ['user_id', 'email', 'locale', 'notification_prefs', 'privacy_prefs', 'auto_share_safety'];

    protected $casts = [
        'user_id' => 'integer',
        'notification_prefs' => 'array',
        'privacy_prefs' => 'array',
        'auto_share_safety' => 'boolean',
    ];
}
