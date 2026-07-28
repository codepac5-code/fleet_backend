<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Service;
use ServiceRoutes;

class ServiceRoutes extends Model
{
    protected $fillable = [
        'serviceId',
        'departureCity',
        'arrivalCity',
        'price',
        'currency',
        'isActive'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
