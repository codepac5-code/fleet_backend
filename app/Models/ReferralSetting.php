<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ReferralSetting extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'referral_settings';

    protected $fillable = [
        'is_active', 'referrer_reward_minor', 'invitee_reward_minor', 'qualifying_rides',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'referrer_reward_minor' => 'integer',
        'invitee_reward_minor' => 'integer',
        'qualifying_rides' => 'integer',
    ];

    /**
     * The active country's programme. A missing row or table means "no referral
     * programme here" — never an error, so a country that never configured one
     * simply has the feature off.
     */
    public static function current(): self
    {
        try {
            $row = self::query()->orderBy('id')->first();
        } catch (Throwable $e) {
            $row = null;
        }

        return $row ?? new self([
            'is_active' => false,
            'referrer_reward_minor' => 0,
            'invitee_reward_minor' => 0,
            'qualifying_rides' => 1,
        ]);
    }
}
