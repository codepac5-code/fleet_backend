<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Vehicle extends Model
{
    use HasFactory , SoftDeletes ;
    use BelongsToOffice;
    use ResolvesTenantConnection;

    protected $table = 'vehicles';
    protected $fillable = [
        'officeId',
        'vehicleBrand',
        'plate',
        'modelYear',
        'licenseNumber',
        'lastDriver',
        'model',
        'color',
        'driverId',
        'subServiceId',
        'city',
        'description',
        'seatsCount',
        'photo',
        'fleet_car',
    ];



    public function scopeForCurrentUser()
    {
        $query = $this->query();

        if (Auth::guard('admin')->check()) {
            return $query;
        }

        if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id);
        }

        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->office_id) {
                return $query->where('officeId', $employee->officeId);
            } else {
                return $query;
            }
        }

        return $query;
    }


    public function office()
    {
        return $this->belongsTo( Office::class , 'officeId' , 'id');
    }

    public function subServices(){
        return $this->belongsToMany(SubService::class, 'vehicle_sub_services', 'vehicleId', 'subServiceId');
    }



    public function vehicleBrand()
    {
        return $this->belongsTo( VehicleBrand::class , 'vehicle_brand_id');
    }


    public function lastDriver()
    {
        return $this->belongsTo(Driver::class, 'last_driver');
    }


    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId','id');
    }



    // public function subService()
    // {
    //     return $this->belongsTo(SubService::class,'sub_service_id');
    // }


    // public function state()
    // {
    //     return $this->belongsTo(State::class,'stateId');
    // }
}
