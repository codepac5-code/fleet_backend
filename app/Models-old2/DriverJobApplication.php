<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DriverJobApplication;

class DriverJobApplication extends Model
{
    protected $fillable = [
        'name',
        'phoneNumber',
        'password',
        // 'officeId',
        'brand',
        'model',
        'year',
        'color',
        'plateNumber',
        'profileImage',
        'idFrontImage',
        'idBackImage',
        'licenseFrontImage',
        'licenseBackImage',
        'mechanicalImage',
        'frontCarImage',
        'backCarImage',
        'rightCarImage',
        'leftCarImage',
        'insideCarImage',
        'frontSeatsImage',
        'backSeatsImage',
        // 'optionalVideo',
        'status',
    ];

}
