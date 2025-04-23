<?php
namespace App\Listeners;

use App\Models\Driver;
use Sk\Geohash\Geohash;
use Illuminate\Support\Facades\Log;
use App\Events\DriverPositionChanged;
use App\Http\Core\Classes\RedisManagerData;
use Illuminate\Support\Facades\Redis;

class DriverPositionChangedListener
{
    /**
     * Handle the event.
     */
    
    public function handle(DriverPositionChanged $event): void
    {
        try {
            $this->storeDriverInArea(
                $event->getDriverId(),
                $event->getLongitude(),
                $event->getLatitude()
            );
        } catch (\Throwable $e) {
            Log::error('Error storing driver position: ' . $e->getMessage());
        }
    }


    private function storeDriverInArea($driverId, $longitude, $latitude, $precision = 6): void
    {
        try {
            
            $driver_model = Driver::where(['id'=>$driverId])->first();
            
            if( $driver_model->isConected ){
                RedisManagerData::makeDriverOnline($driverId , $latitude, $longitude, $precision);
               // $g = new Geohash();
               // $geoHash = $g->encode($latitude, $longitude, $precision);
            //    $geoHash = RedisManagerData::getGeoHash($latitude, $longitude, $precision);
               // $areaKey = "drivers-area:".$geoHash;
                
                // if(RedisManagerData::exists('driver.'.$driverId.':area')){
                //     $area    = Redis::get('driver.'.$driverId.':area');
                //     if($area != $geoHash ){
                //     $keyB = "drivers-area:$area";
                //     Redis::zrem($keyB, $driverId);
                //     Redis::del('driver.'.$driverId.':area');
                //     }
                // }
                
                //'driver.1:area'
                // storeToRedis('driver.'. $driverId .':area' , $geoHash , 86400);

                // Redis::geoadd($areaKey , $longitude , $latitude, $driverId);
                // Redis::expire($areaKey , 3600 * 12 );
                // Log::info("Driver $driverId added to area $geoHash");
            }


        } catch (\Throwable $e) {
            Log::error("Error adding driver to Redis: " . $e->getMessage());
        }
    }
}
