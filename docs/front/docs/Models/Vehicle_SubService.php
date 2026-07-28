<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle_SubService extends Model
{
    use HasFactory  ;
    protected $table = 'vehicle_sub_services';
    protected $fillable = [
        'subServiceId', 
        'vehicleId', 
    ];

}
