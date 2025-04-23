<?php

namespace App\Jobs;

use App\Events\SearchOnDriver;
use App\Events\DriverPositionChanged;
use App\Events\NewOrder;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandelRedisEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct( private $event , private $data )
    {
        //
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
         print('s5555555511');

         //print_r($this->data['driverId']);
        $this->{$this->event}($this->data);
    }


    public function driver_position_changed( $data): void
    {
        event(new DriverPositionChanged($data['driverId'] , $data['driverLatitude'] ,  $data['driverLongitude']));
      // new DriverPositionChangedListener( new DriverPositionChanged($data['driverId'] , $data['latitude'] ,  $data['longitude']));
    }



    public function research_on_driver($data){
        broadcast(new SearchOnDriver($data));
    }

    public function new_order($data){
        broadcast(new NewOrder($data['data'] , $data['driverId'] ));
    }


    public function delete_order($data){
        // $orderId = $data['orderId'];
        // $key = 'order.'. $orderId.':notAcceptedByDriver';
        // if(Redis::exists($key)){
        // $r_data = readArrayFromRedis($key);
        // $driverIds = $r_data['driverIds'];
        // foreach($driverIds as $driverId){
          broadcast(new \App\Events\DeleteOrder($data['orderId'] , $data['driverId'] ));
        //}

        // $driverIds = Redis::get();
        // foreach ($driverIds as $driverId) {
        //     event(new \App\Events\DeleteOrder($orderId, $driverId));
        // }
    }

    // public function delete_order_after_accept($data){;
    //     // $orderId = $data['orderId'];
    //     // $key = 'order.'. $orderId.':notAcceptedByDriver';
    //     // if(Redis::exists($key)){
    //     // $r_data = readArrayFromRedis($key);
    //     // $driverIds = $r_data['driverIds'];
    //     // foreach($driverIds as $driverId){
    //       broadcast(new \App\Events\DeleteOrder($data['orderId'] , $data['driverId'] ));
    //     //}
        
    //     // $driverIds = Redis::get();
    //     // foreach ($driverIds as $driverId) {
    //     //     event(new \App\Events\DeleteOrder($orderId, $driverId));
    //     // }
    // }

}

