<?php
namespace App\Http\Core\Algorithms;

use App\Jobs\SendNewOrderForDriversJob;
use App\Models\Driver;
use Sk\Geohash\Geohash;
use App\Events\NewOrder;
use App\Events\SearchOnDriver;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Driver\DriverRedisModel;
use Illuminate\Support\Facades\Log;


class SearchOnDriverAlgorithm
{


    /**
     * Create a new job instance.
     */
    public function __construct(public array $data , private int $attempt = 1) {}


    /**
     * Execute the job.
     */
    public function start()
    {
        // print('searchhhhhhhhhhhhhh');
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
        //     $this->attempt += 1;
        //     $this->start();
        // }

    }


    private function notifyNearbyDrivers($longitude, $latitude, $radius, $orderId , $data): void
    {
        // print('notifyNearbyDrivers');

        try {

            RedisManagerData::setOrderNotAccepted($orderId);

            $driverIds = $this->findDriversInNearbyAreas($latitude, $longitude,  $orderId , $radius);



            if ( !empty($driverIds) ) {


                //-----------------
                $drivers = Driver::select('id', 'is_online')->whereIn('id', $driverIds)
                 ->where('is_online', true)
                 ->get();

                $sub_service_Id = $data['subServiceId'];

                $driverIds = Driver::query()->whereIn('id', $driverIds)
                 ->where('is_online', true)
                 ->whereHas('vehicle', function ($q) use ($sub_service_Id) {
                    $q->whereHas('subServices', function ($qq) use ($sub_service_Id) {
                        $qq->where('subServiceId', $sub_service_Id);
                    });
                })->pluck('id')->toArray();

                if(empty($driverIds)){
                    Log::info("No drivers found with the required sub service.");
                    return;
                }

                //-------------------------------- Send New Order to drivers --------------------------------
                SendNewOrderForDriversJob::dispatch($driverIds , $data)->onQueue('jobs');//|
                //-------------------------------------------------------------------------------------------

                $notified_drivers = $driverIds;



                // foreach($drivers as $driver){.
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

        // print("\n".'findDriversInNearbyAreas');
        $g = new Geohash();
        $userGeoHash = $g->encode($latitude, $longitude, 6);
        info('user area:'.$userGeoHash);

        $neighboringAreas = $this->getNeighboringGeoHashes($userGeoHash);
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
            // print("\n".'getNeighboringGeoHashes');
            // print($hash);

        $g = new Geohash();
        return $g->getNeighbors($hash);
    }

}

