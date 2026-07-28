<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use City;
use Country;

class Country extends Model
{
    use HasFactory;
    protected $connection = 'global';
    protected $table = "countries";
    protected $primaryKey = "id";

    protected $fillable = [
        'iso2',
        'iso3',
        'name',
        'en_name',
        'name_on_google_map',
        'phone_code',
        'currency_code',
        'currency_symbol',
        'timezone',
        'flag',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'phone_code' => 'string',
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    public function cities()
    {
        return $this->hasMany(City::class, 'countryId', 'id');
    }
}
