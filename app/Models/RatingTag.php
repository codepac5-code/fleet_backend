<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class RatingTag extends Model
{
    use ResolvesTenantConnection;

    public const AUDIENCE_RIDER = 'rider';
    public const AUDIENCE_DRIVER = 'driver';
    public const AUDIENCE_BOTH = 'both';

    protected $table = 'rating_tags';

    protected $fillable = [
        'code', 'label_en', 'label_ar', 'audience', 'stars_min', 'stars_max', 'sort', 'is_active',
    ];

    protected $casts = [
        'stars_min' => 'integer',
        'stars_max' => 'integer',
        'sort' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Active tags offered to an audience (its own + shared 'both'), narrowed to
     * the star rating being given when one is known.
     */
    public function scopeForAudience($query, string $audience, ?int $stars = null)
    {
        return $query
            ->where('is_active', true)
            ->whereIn('audience', [$audience, self::AUDIENCE_BOTH])
            ->when($stars !== null, fn ($q) => $q->where('stars_min', '<=', $stars)->where('stars_max', '>=', $stars))
            ->orderBy('sort')
            ->orderBy('id');
    }
}
