<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use OfficeAddress;

class OfficeAddress extends Model
{
    use BelongsToOffice;
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
