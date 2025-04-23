<?php
namespace App\Listeners\BaseRedisListener;


use App\Events\DriverPositionChanged;
use App\Events\SearchOnDriver;
use App\Listeners\DriverPositionChangedListener;
use App\Listeners\SearchOnDriverListener;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ListenToRedisEvents
{
    /**
     * Create the event listener.
     */
    public function __construct()
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
     * Handle the event.
     */
    public function driver_position_changed( $data): void
    {
       new DriverPositionChangedListener( new DriverPositionChanged($data['driverId'] , $data['latitude'] ,  $data['longitude']));
    }

    public function research_on_driver($data){

        print_r($data);
        new SearchOnDriverListener('');
    }


}
