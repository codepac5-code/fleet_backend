<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Driver;
use Office;
use OfficeDriverCustomCommission;

class OfficeDriverCustomCommission extends Model
{
    use HasFactory;
    use BelongsToOffice;



    protected $fillable = [
        'driverId',
        'officeId',
        'driverCommission',
        'officeCommission',
    ];


    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
