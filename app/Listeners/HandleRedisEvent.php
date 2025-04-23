<?php

namespace App\Listeners;

use App\Log;
use App\Events\MyRedisEvent;
use Illuminate\Support\Facades\Log as FacadesLog;

class HandleRedisEvent
{
    /**
     * Handle the event.
     */
    public function handle(MyRedisEvent $event): void
    {
         FacadesLog::info('Received Redis event:', ['data' =>"isten.......77776768678587467"]);

        print('listen.......77776768678587467');
        
    }
}
