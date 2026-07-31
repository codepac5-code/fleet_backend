<?php

namespace App\Models;

use App\Traits\StampsActiveCountry;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use StampsActiveCountry;

    public const PENDING = 'pending';
    public const REWARDED = 'rewarded';

    protected $connection = 'global';

    protected $table = 'referrals';

    protected $fillable = [
        'referrer_user_id', 'invitee_user_id', 'code', 'status', 'qualifying_booking_id',
        'country_code', 'referrer_reward_minor', 'invitee_reward_minor', 'currency_code', 'rewarded_at',
    ];

    protected $casts = [
        'referrer_user_id' => 'integer',
        'invitee_user_id' => 'integer',
        'qualifying_booking_id' => 'integer',
        'referrer_reward_minor' => 'integer',
        'invitee_reward_minor' => 'integer',
        'rewarded_at' => 'datetime',
    ];
}
