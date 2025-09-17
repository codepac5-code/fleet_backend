<?php

namespace App\Jobs;

use App\Models\Driver;
use Sk\Geohash\Geohash;
use App\Events\NewOrder;
use App\Events\SearchOnDriver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;

class SearchOnDriverJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct( private SearchOnDriver $event)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        print('searchhhhhhhhhhhhhh');
        try {

            $this->notifyNearbyDrivers(
                $this->event->getLongitude(),
                $this->event->getLatitude(),
                $this->event->getRadius(),
                $this->event->getOrderId(),
                $this->event->getData()
            );
        } catch (\Throwable $e) {
            Log::error("Error searching for drivers: " . $e->getMessage());
        }
    }

   
    private function notifyNearbyDrivers($longitude, $latitude, $radius, $orderId , $data): void
    {
        print('notifyNearbyDrivers');

        try {
            //order.1:notAcceptedByDriver    
            $key = 'order.'.$orderId.':notAcceptedByDriver';      
            // if(Redis::exists($key)){
            //     $order_info = readArrayFromRedis($key);
            //     $radius = ($order_info['radius'] + 0.2 < 2) ? $order_info['radius'] + 0.2 : $order_info['radius'];
            // }
            // print('rrrrrrrrrrrrrrrrrrrrrrrrrr');
            // order.93:notAcceptedByDriver
            
            $driverIds = $this->findDriversInNearbyAreas($latitude, $longitude,  $orderId , $radius);
            
            $order_info = [
                'driverIds'=> $driverIds,
                'radius'   => $radius
            ];
            storeArrayToRedis( $key , $order_info , 1800 );


            if ( !empty($driverIds) ) {

                
                //-----------------
                $drivers = Driver::whereIn('id', $driverIds)
                 ->where('isConected', true)
                 ->select('id', 'isConected')
                 ->get();
                 
                 
                 foreach($drivers as $driver){
                     //   broadcast(new NewOrder($data, $driver->id));
                     event((new NewOrder($data, $driver->id)));

                        // dispatch(new HandelRedisEvents('new_order', [
                        //     'data' => $data,
                        //     'driverId' => $driverId,
                        // ]));
                    }

                // foreach($driverIds as $driverId){
                //         dispatch(new HandelRedisEvents('new_order', [
                //             'data' => $data,
                //             'driverId' => $driverId,
                //         ]));
                //     }


                // $drivers = Driver::whereIn('id', $driverIds)->where('isConected',true);
                // foreach($drivers as $driver){
                //     info('ddddddddddddd');

                //     info('notify driver :'.$driver->id);
                //     info('dispatch driver :'.$driver->id);
                //     print('dispatch driver :'.$driver->id);

                //     dispatch(new HandelRedisEvents('new_order', [
                //         'data' => $data,
                //         'driverId' => $driver->id,
                //     ]));
                 //   broadcast(new NewOrder($data, $driver->id));
              //  }
             // $driverIds = array_values($driverIds);

                // array_map(function ($driverId , $data) {
                //  dispatch(new HandelRedisEvents('new_order', [
                //             'data' => $data,
                //             'driverId' => $driverId,
                //         ]));
                //     // broadcast(new NewOrder($data, $driverId));
                // }, $driverIds , $data);

                Log::info("Notified drivers: " . implode(',', $driverIds));

            } else {
                Log::info("No drivers found in the specified radius.");
            }
        } catch (\Throwable $e) {
            Log::error("Error notifying drivers: " . $e->getMessage());
        }
    }






    private function findDriversInNearbyAreas($latitude, $longitude,  $orderId , $radius = 1 ): array
    {
    
        print("\n".'findDriversInNearbyAreas');
        $g = new Geohash();
        $userGeoHash = $g->encode($latitude, $longitude, 6);
        info('user area:'.$userGeoHash);

        $neighboringAreas = $this->getNeighboringGeoHashes($userGeoHash);
        info('nieee');
        $neighboringAreas['userArea'] = $userGeoHash;
        
        info('nieee',$neighboringAreas);

        $drivers = [];

        foreach ($neighboringAreas as $area) {
            $areaKey = 'drivers-area:'.$area;
            info($areaKey);

            try {
                $areaDrivers = Redis::georadius($areaKey , $longitude , $latitude , $radius , 'km');
                info('areaDrivers:' , $areaDrivers);
                $drivers = array_merge( $drivers , $areaDrivers );
            }  catch (\Throwable $e) {
                Log::error("Error fetching drivers from area $area: " . $e->getMessage());
            }
        }
    
     //   print('7877788888');
        // info('driverssss :');
      info('driverssss :',array_unique($drivers));
       // info('driverss :'.array_unique($drivers));
    //   $drivers_int = array_map('intval', $drivers);

    $drivers_unique = array_unique($drivers);
    return $drivers_int = array_map('intval', $drivers_unique);

}


    private function getNeighboringGeoHashes($hash): array
    {
            print("\n".'getNeighboringGeoHashes');
            print($hash);

        $g = new Geohash();
        return $g->getNeighbors($hash);
    }

}



// NewOrder::dispatch([
//     'data' => $data,
//     'driverId' => $driver->id,
//  ])->onQueue('events');