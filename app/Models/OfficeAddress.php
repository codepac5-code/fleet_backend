<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeAddress extends Model
{
    protected $table = 'office_addresses';
    protected $fillable = [
        'address',
        'addressName',
        'town',
        'phone',
        'description',
        'lat',
        'lang',
        'officeId'
    ];
}
