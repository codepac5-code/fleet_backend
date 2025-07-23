<?php

namespace App\Jobs;

use App\Models\Driver;
use Sk\Geohash\Geohash;
use App\Events\NewOrder;
use App\Events\SearchOnDriver;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Driver\DriverRedisModel;
use App\Models\Booking;
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
    public function __construct(public array $data , private int $attempt = 1) {}


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        print('searchhhhhhhhhhhhhh');
        $search = new SearchOnDriver($this->data);

        try {

            $this->notifyNearbyDrivers(
                $search->getLongitude(),
                $search->getLatitude(),
                $search->getRadius(),
                $search->getOrderId(),
                $search->getData()
            );
        } catch (\Throwable $e) {
            Log::error("Error searching for drivers: " . $e->getMessage());
        }


        // if ($this->attempt < 3 && RedisManagerData::OrderNotAccepted($search->getOrderId()) ) {
        //     SearchOnDriverJob::dispatch($this->data, $this->attempt + 1)
        //         ->delay(now()->addSeconds(9))
        //         ->onQueue('jobs');
        //}

    }

   
    private function notifyNearbyDrivers($longitude, $latitude, $radius, $orderId , $data): void
    {
        print('notifyNearbyDrivers');

        try {
            
            RedisManagerData::setOrderNotAccepted($orderId);
            
            $driverIds = $this->findDriversInNearbyAreas($latitude, $longitude,  $orderId , $radius);
            
        

            if ( !empty($driverIds) ) {

                
                //-----------------
                $drivers = Driver::select('id', 'isConected')->whereIn('id', $driverIds)
                 ->where('isConected', true)
                 ->get();
                
                $notified_drivers =[];

                foreach ($drivers as $driver) {
                    $driverId = $driver->getAttribute('id'); 
                    $subServices = DriverRedisModel::getDriverServices($driverId);
                
                    $sub_service_Id = $data['subServiceId'];
                    if (is_array($subServices) && in_array($sub_service_Id, $subServices)) {
                        info('driver has sub service: ' . $sub_service_Id);
                        broadcast(new NewOrder($data, $driverId));
                        $notified_drivers[] = $driverId;

                    }else{
                        info('driver >> driverId ='.$driverId.' >> has no service: ' . $sub_service_Id);
                    }
                }
                // foreach($drivers as $driver){
                //     $subServices = DriverRedisModel::getDriverServices($driver->id );

                //     $sub_service_Id = $data['subServiceId'];
                //     // info('sub service:'.$sub_service_Id);
                //     if(in_array($sub_service_Id , $subServices)){
                //         array_push($notified_drivers , $driver->id);
                //         info('driver has sub service: '.$sub_service_Id);
                //         broadcast((new NewOrder($data, $driver->id)));
                //     }
            
                // }
  
                // $driverIds = array_values($driverIds);

                // array_map(function ($driverId , $data) {
                //  dispatch(new HandelRedisEvents('new_order', [
                //             'data' => $data,
                //             'driverId' => $driverId,
                //         ]));
                //     // broadcast(new NewOrder($data, $driverId));
                // }, $driverIds , $data);

                    $order_info = [
                        'driverIds'=> !empty($notified_drivers) ? $notified_drivers :[],
                        'radius'   => $radius
                    ];
            
                    RedisManagerData::storeOrderDetails($orderId , $order_info);
                
                if(empty($notified_drivers)){
                    Log::info("No drivers found in the specified radius.");
                }
                else{
                    Log::info("Notified drivers: " . implode(',', $notified_drivers));
                }

            } else {
                
                $order_info = [
                    'driverIds'=> [],
                    'radius'   => $radius
                ];
        
                RedisManagerData::storeOrderDetails($orderId , $order_info);
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
            // $areaKey = 'drivers-area:'.$area;
            // info($areaKey);
            try {
                $drivers_in_area = RedisManagerData::get_drivers_in_area_by_radius($longitude , $latitude , $radius , $area);
                //  Redis::georadius($areaKey , $longitude , $latitude , $radius , 'km');
                info('drivers_in_area:' , $drivers_in_area);
                $drivers = array_merge( $drivers , $drivers_in_area );
            }  catch (\Throwable $e) {
                Log::error("Error fetching drivers from area $area: " . $e->getMessage());
            }
        }
    
     //   print('7877788888');
        // info('driverssss :');
      info('driverssss :',array_unique($drivers));
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

