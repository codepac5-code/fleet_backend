<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
    use ResolvesTenantConnection;

    public const AUDIENCE_RIDER = 'rider';
    public const AUDIENCE_DRIVER = 'driver';
    public const AUDIENCE_BOTH = 'both';

    protected $table = 'cancellation_reasons';

    protected $fillable = [
        'code', 'label_en', 'label_ar', 'audience', 'sort', 'is_active',
    ];

    protected $casts = [
        'sort' => 'integer',
        'is_active' => 'boolean',
    ];

    /** Active reasons shown to an audience (its own + shared 'both'), ordered. */
    public function scopeForAudience($query, string $audience)
    {
        return $query
            ->where('is_active', true)
            ->whereIn('audience', [$audience, self::AUDIENCE_BOTH])
            ->orderBy('sort')
            ->orderBy('id');
    }
}
