<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
