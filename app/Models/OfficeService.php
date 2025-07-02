<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeService extends Model
{
    protected $fillable = [
        'officeId',
        'status',
        'serviceId',
    ];

}
