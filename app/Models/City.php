<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Cities live with the corridors that reference them. `countries` stays on the
 * platform, but a corridor row in `fleet_sy` joining a city row in `fleet` is a
 * join across two databases held together by nothing but matching ids.
 */
class City extends Model
{
    use HasFactory;
    use ResolvesTenantConnection;

    protected $table = "cities";
    protected $primaryKey = "id";

    protected $fillable = [
        'name',
        'name_on_google_map',
        'countryId',
        'en_name',
    ];

    protected $casts = [
        'countryId' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'countryId', 'id');
    }
}
