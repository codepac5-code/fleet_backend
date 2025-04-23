<?php
namespace App\Listeners;

use App\Models\Driver;
use Sk\Geohash\Geohash;
use App\Events\NewOrder;
use App\Events\SearchOnDriver;
use App\Jobs\HandelRedisEvents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


class SearchOnDriverListener
{
    /**
     * Handle the event.
     */
    public function handle(SearchOnDriver $event): void
    {
        print('searchhhhhhhhhhhhhh');

        try {

            $this->notifyNearbyDrivers(
                $event->getLongitude(),
                $event->getLatitude(),
            1,
                $event->getOrderId(),
                $event->getData()
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
                // $drivers = Driver::select('id')->whereIn('id', $driverIds)->where('isConected',true)->get();
                    foreach($driverIds as $driverId){
                     //   broadcast(new NewOrder($data, $driver->id));
                        dispatch(new HandelRedisEvents('new_order', [
                            'data' => $data,
                            'driverId' => $driverId,
                        ]));
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

                $areaDrivers = Redis::georadius($areaKey, $longitude, $latitude, 3, 'km');
        info('areaDrivers:',$areaDrivers);
                $drivers = array_merge($drivers, $areaDrivers);
            } catch (\Throwable $e) {
                Log::error("Error fetching drivers from area $area: " . $e->getMessage());
            }
        }
    
     //   print('7877788888');
        // info('driverssss :');
      info('driverssss :',array_unique($drivers));
       // info('driverss :'.array_unique($drivers));
    //   $drivers_int = array_map('intval', $drivers);

        return array_unique($drivers);
    }


    private function getNeighboringGeoHashes($hash): array
    {
            print("\n".'getNeighboringGeoHashes');
            print($hash);

        $g = new Geohash();
        return $g->getNeighbors($hash);
    }
}






// function removeDriverFromRedis($driverId, $longitude, $latitude, $precision = 6)
// {
//     try {
//         $g = new \Sk\Geohash\Geohash();
//         $geoHash = $g->encode($latitude, $longitude, $precision);
//         $areaKey = "drivers:area:$geoHash";

//         $result = Redis::zrem($areaKey, $driverId);

//         if ($result) {
//             Log::info("Driver $driverId removed from area $geoHash");
//             return true;
//         } else {
//             Log::warning("Driver $driverId not found in area $geoHash");
//             return false;
//         }
//     } catch (\Throwable $e) {
//         Log::error("Error removing driver $driverId: " . $e->getMessage());
//         return false;
//     }
// }
