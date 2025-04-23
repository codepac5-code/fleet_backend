<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
    use HasFactory;
    protected $table = "cities";
    protected $primaryKey = "id";
    
    protected $casts = [
        'countryId'    => 'integer',
    ];
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'countryId','id');
    }

	public function cities()
    {
        return $this->hasMany(City::class, 'countryId','id');
    }
}
