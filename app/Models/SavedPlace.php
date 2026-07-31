<?php

namespace App\Models;

use App\Traits\StampsActiveCountry;
use Illuminate\Database\Eloquent\Model;

class SavedPlace extends Model
{
    use StampsActiveCountry;

    protected $connection = 'global';

    protected $table = 'saved_places';

    protected $fillable = ['user_id', 'driver_id', 'country_code', 'label', 'icon', 'title', 'address', 'lat', 'lng'];

    /**
     * A driver's places, scoped to the country they are signed into. Riders are
     * global accounts so their places are NOT country-scoped — a driver's are,
     * because driver ids repeat across country databases.
     */
    public function scopeForDriver($query, int $driverId)
    {
        $country = self::activeCountryCode();

        return $query
            ->where('driver_id', $driverId)
            ->when($country !== null, fn ($q) => $q->where(fn ($w) => $w->where('country_code', $country)->orWhereNull('country_code')));
    }

    protected $casts = [
        'user_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
    ];
}
