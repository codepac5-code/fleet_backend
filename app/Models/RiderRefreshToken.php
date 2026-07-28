<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderRefreshToken extends Model
{
    protected $table = 'rider_refresh_tokens';

    protected $fillable = ['user_id', 'token_hash', 'expires_at'];

    protected $casts = [
        'user_id' => 'integer',
        'expires_at' => 'datetime',
    ];
}
