<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use City;
use Country;

class City extends Model
{
    use HasFactory;

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
