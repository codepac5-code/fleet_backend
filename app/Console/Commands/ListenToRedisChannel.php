<?php
namespace App\Console\Commands;

use App\Events\TestEvent;
use App\Jobs\HandelRedisEvents;
use App\Listeners\TestListener;
use Illuminate\Console\Command;
use App\Jobs\ProcessFlutterEvent;
use Illuminate\Support\Facades\Redis;
use App\Listeners\BaseRedisListener\ListenToRedisEvents;

class ListenToAllRedisChannels extends Command
{
    protected $signature = 'redis:listen';

    protected $description = 'Listen for messages from all Redis channels';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        print('listen to redis channels..');

        //,'driver.*'
        Redis::connection('pubsub')->psubscribe(['user.*','driver.*'], function ($data) {
             $data = json_decode($data, true);


            // print_r($data);
           // print_r("\n".$data['data']);

         // print("receive event from  redis :".$channel." data: ".$data."\n");
         //print('socket:'.$data['socket']);

            if(isset($data['socket']) && $data['socket'] == true){
                print("\n".$data['event']);
              //  print("receive event from  redis :".$channel." data: ".$data."\n");
                //print("\n".'dispatchhhh');
                dispatch(new HandelRedisEvents($data['event'],$data['data']));
            }

           // $this->info('Event received from Redis: ' . $message);
        });

    }


}
