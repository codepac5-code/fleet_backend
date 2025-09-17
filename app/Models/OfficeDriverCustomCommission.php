<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeDriverCustomCommission extends Model
{
    use HasFactory;



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
