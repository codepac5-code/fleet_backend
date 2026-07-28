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
    public $queue = 'reminders';
    /**
     * Create a new event instance.
     */
    public function __construct(private $data , private $driverId)
    {
        info('reminder driver event fired');
    }

    public function broadcastOn()
    {
            return new Channel('driver.' . $this->driverId);
    }

    public function broadcastAs(){
        return 'ReminderScheduledOrder';
    }

    public function broadcastWith(){
        info('dddddddddddddddddiu');

        // info($this->data);
        return $this->data;
    }
}
