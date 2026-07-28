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
        'serviceId',
        'is_travel',
        'name_en',
        'description_en'
    ];

    protected $hidden = [
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



    public function  createWithRoutes(array $data, ?array $routes = null) {
        $this->create($data);
        TravelRoutes::where('sub_service_id', $this->id)->delete();

        if ($routes !== null) {

            $formatted = collect($routes)->map(function ($r) {
                return [
                    'sub_service_id' => $this->id,
                    'departure_city' => $r['departureCity'],
                    'arrival_city'   => $r['arrivalCity'],
                    'trip_price'     => $r['tripPrice'],
                ];
            })->toArray();

            TravelRoutes::insert($formatted);
        }

        return $this;
    }



    public function updateWithRoutes(array $data, ?array $routes = null)
    {
    $this->update($data);
    TravelRoutes::where('sub_service_id', $this->id)->delete();

    if ($routes !== null) {

        $formatted = collect($routes)->map(function ($r) {
            return [
                'sub_service_id' => $this->id,
                'departure_city' => $r['departureCity'],
                'arrival_city'   => $r['arrivalCity'],
                'trip_price'     => $r['tripPrice'],
            ];
        })->toArray();

        TravelRoutes::insert($formatted);
    }

    return $this;
    }

    public function syncTravelRoutes(array $routes)
    {
        $formatted = collect($routes)->map(function ($r) {
            return [
                'sub_service_id' => $this->id,
                'departure_city' => $r['departureCity'],
                'arrival_city'   => $r['arrivalCity'],
                'trip_price'     => $r['tripPrice'],
            ];
        })->toArray();

        TravelRoutes::where('sub_service_id', $this->id)->delete();

    TravelRoutes::insert($formatted);
    }

    public function travelRoutes($officeId = null){
            $query = $this->hasMany(TravelRoutes::class);

            if($officeId){
                return $query->where('officeId', $officeId);
            }

            return $query;
        }

    public function officePrices()
    {
        return $this->hasMany(OfficeSubServicePrice::class);
    }

    public function offices()
    {
        return $this->belongsToMany(Office::class, 'office_sub_service_prices')
            ->withPivot(['openPrice', 'kmPrice', 'minutePrice'])
            ->withTimestamps();
    }


    // public function routes()
    // {
    //     return $this->hasMany(TravelRoutes::class)->where('is_travel', 1);
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
