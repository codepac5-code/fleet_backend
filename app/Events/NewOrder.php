<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable; 
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewOrder implements ShouldBroadcast , ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'events';


    /**
     * Create a new event instance.
     */
    public function __construct(private $data , private $driverId)
    {}

    public function broadcastOn()
    {
            return new Channel('driver.' . $this->driverId);
    }

    public function broadcastAs(){
        return 'new_order';
    }


    public function broadcastWith(){
        return $this->data;
    }
}
