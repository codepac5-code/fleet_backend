<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubService extends Model implements HasMedia
{
    use InteractsWithMedia  , SoftDeletes  , HasFactory; 

    protected $table = 'sub_services';

    protected $fillable = [
        'name',
        'image',
        'status',
        'description',
        'openPrice',
        'kmPrice',
        'minutePrice',
        'subServiceId',
        'name_en',
        'description_en'
    ];

    protected $hidden = [
        'openPrice',
        'kmPrice',
        'minutePrice',
        // 'status',
    ];

    // public static function SelectWithTranslate(){

    //     switch(app()->getLocale())
    //     {
    //         case 'ar': 
    //             return SubService::select([
    //                 'name',
    //                 'image',
    //                 'status',
    //                 'description',
    //                 'openPrice',
    //                 'kmPrice',
    //                 'minutePrice',
    //                 'serviceId',
    //             ]);
          
    //         case 'en': 
    //             return SubService::select([
    //                 'name_en as name',
    //                 'description_en as description',
    //                 'image',
    //                 'status',
    //                 'openPrice',
    //                 'kmPrice',
    //                 'minutePrice',
    //                 'serviceId',

    //             ]);

    //         default :
    //         return SubService::select([
    //             'name',
    //             'image',
    //             'status',
    //             'description',
    //             'openPrice',
    //             'kmPrice',
    //             'minutePrice',
    //             'serviceId',
    //           ]);
    //     }
     

    // }

    /**
     * Get all of the vehicle for the subService
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vehicle()
    {
        return $this->hasMany(Vehicle::class, 'serviceId');
    }

    /**
     * Get the service that owns the subService
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'serviceId');
    }
    
}
