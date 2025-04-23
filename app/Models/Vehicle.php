<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory , SoftDeletes ;
    protected $table = 'vehicles';
    protected $fillable = [
        'officeId', 
        'vehicleBrand', 
        'plate', 
        'modelYear', 
        'licenseNumber', 
        'lastDriver', 
        'model', 
        'color', 
        'driverId', 
        'subServiceId', 
        'city',
        'description',  
        'seatsCount',  
        'photo',
        'fleet_car',
    ];



    public function office()
    {
        return $this->belongsTo( Office::class , 'officeId' , 'id');
    }

    public function subServices(){       
    return $this->belongsToMany(SubService::class, 'vehicle_sub_services', 'vehicleId', 'subServiceId');
    }



    public function vehicleBrand()
    {
        return $this->belongsTo( VehicleBrand::class , 'vehicle_brand_id');
    }

    
    public function lastDriver()
    {
        return $this->belongsTo(Driver::class, 'last_driver');
    }

  
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId','id');
    }


   
    // public function subService()
    // {
    //     return $this->belongsTo(SubService::class,'sub_service_id');
    // }

  
    // public function state()
    // {
    //     return $this->belongsTo(State::class,'stateId');
    // }
}
