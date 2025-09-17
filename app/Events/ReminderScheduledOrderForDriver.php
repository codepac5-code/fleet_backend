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

class ReminderScheduledOrderForDriver implements ShouldBroadcast , ShouldQueue
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
        return 'ReminderScheduledOrder';
    }

    public function broadcastWith(){
        return $this->data;
    }
}
