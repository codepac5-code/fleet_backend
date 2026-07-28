<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use FleetOffice;

class FleetOffice extends Model
{

    protected $table = 'fleet_office';

    protected $fillable = [
        'fleet_commission_value_with_driver',
        'fleet_commission_value_with_office',
        // 'office_commission_type',
        'office_commission_value',
        'driver_commission_value',
        // 'driver_commission_type',
        'walletBalance',

        //--------------------

        'total_income',
        'withdrawn_amount',
        'available_amount',
        'drivers_debt',
        'offices_debt',
        'users_count',
        'drivers_count',
        'offices_count',
        'services_count',
        'subServices_count',
    ];
}
