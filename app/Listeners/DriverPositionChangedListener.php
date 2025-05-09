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
                
            } else
            {
              RedisManagerData::makeDriverOffline($driverId);
            }


        } catch (\Throwable $e) {
            Log::error("Error adding driver to Redis: " . $e->getMessage());
        }
    }
}
