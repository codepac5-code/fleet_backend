<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HoldOrder implements ShouldBroadcast ,ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'events';

    /**
     * Create a new event instance.
     */
    public function __construct(private $orderId)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()//: array
    {

        return new Channel('order.'.$this->orderId);


    }


    public function broadcastAs(){
        return 'hold-order';
    }
}
