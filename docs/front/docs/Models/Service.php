<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Service extends  Model implements HasMedia
{
    use InteractsWithMedia  , SoftDeletes , HasFactory;


    protected $table = 'services';

    protected $fillable = [
        'name',
        'status',
        'description',
        'image',
        'title',
        'title_en',
        'description_en',
        'travel_service',
    ];


    public static function SelectWithTranslate(){

        switch(app()->getLocale())
        {
            case 'ar':
                return Service::select([
                  'status',
                  'description',
                  'image',
                  'title',
                ]);

            case 'en':
                return Service::select([
                  'status',
                  'title_en as title',
                  'description_en as description',
                  'image',
                ]);

            default :
            return Service::select([
                'status',
                'description',
                'image',
                'title',
              ]);
        }


    }

    public function scopeForCurrentUser($query){
    $query = $query->with(['subServices.travelRoutes']);

    if (Auth::guard('admin')->check()) {
        return $query;
    }

    if (Auth::guard('office')->check()) {
        $office = Auth::guard('office')->user();
        return $query->whereHas('offices', function($q) use ($office) {
            $q->where('offices.id', $office->id);
        });
    }

    if (Auth::guard('employee')->check()) {
        $employee = Auth::guard('employee')->user();
        if ($employee->officeId) {
            return $query->whereHas('offices', function($q) use ($employee) {
                $q->where('offices.id', $employee->officeId);
            });
        } else {
            return $query;
        }
    }

    return $query;
    }


    /**
     * Get all of the subServices for the Service
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subServices()
    {
        return $this->hasMany(SubService::class , "serviceId");
    }
    public function offices()
    {
    return $this->belongsToMany(Office::class, 'office_services', 'serviceId', 'officeId');
    }

}
