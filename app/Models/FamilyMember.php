<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * B2B family / guardian rider. Backs GET|POST|PATCH|DELETE /family/members.
 * @see migration 2026_07_15_000001_add_rider_api_missing_columns
 */
class FamilyMember extends Model
{
    protected $connection = 'global';

    protected $table = 'family_members';

    protected $fillable = [
        'user_id', 'name', 'phone', 'type', 'approval_required', 'auto_share',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'approval_required' => 'boolean',
        'auto_share' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
