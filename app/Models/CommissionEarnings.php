<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionEarnings extends Model
{
    protected $table = 'commission_earnings';
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id', 
        'driver_id', 
        'office_id', 
        'fleet_id',
        'total_fare',
        'office_commission',
        'driver_commission',
        'fleet_commission',
        'payment_date',
        'commission_status'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function fleet()
    {
        return $this->belongsTo(FleetOffice::class, 'fleet_id');
    }
}
