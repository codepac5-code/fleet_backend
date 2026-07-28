<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DriverAddress;

class DriverAddress extends Model
{
    protected $table = 'driver_addresses';

    protected $fillable = [
        'address',
        'addressName',
        'town',
        'phone',
        'description',
        'lat',
        'lang',
        'driverId'
    ];


}
