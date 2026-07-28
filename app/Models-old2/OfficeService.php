<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use OfficeService;

class OfficeService extends Model
{
    use BelongsToOffice;
    protected $fillable = [
        'officeId',
        'status',
        'serviceId',
    ];

}
