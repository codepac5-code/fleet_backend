<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pinned to the platform, like the rider it belongs to.
 *
 * A rider account is GLOBAL — one person, one login, whatever country they open
 * the app in. The refresh token is an artifact of that account, so it must not
 * follow the shard: rows had already split 51/20 across two databases, which
 * means a rider who crossed a border could refresh in one country and be logged
 * out in the other, depending on which shard answered.
 */
class RiderRefreshToken extends Model
{
    protected $connection = 'global';

    protected $table = 'rider_refresh_tokens';

    protected $fillable = ['user_id', 'token_hash', 'expires_at'];

    protected $casts = [
        'user_id' => 'integer',
        'expires_at' => 'datetime',
    ];
}
