<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use City;
use Office;
use SubService;
use TravelRoutes;


class TravelRoutes extends Model
{
    protected $fillable = [
        'sub_service_id',
        'departure_city_id',
        'arrival_city_id',
        'trip_price',
        'officeId',
    ];

    public function subService()
    {
        return $this->belongsTo(SubService::class);
    }

    public function departureCity()
    {
        return $this->belongsTo(City::class, 'departure_city_id');
    }

    public function arrivalCity()
    {
        return $this->belongsTo(City::class, 'arrival_city_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId');
    }
}
