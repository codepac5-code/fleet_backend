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

class DeleteOrder implements ShouldBroadcast ,ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'events';

    /**
     * Create a new event instance.
     */
    public function __construct(private $orderId , private $driverId)
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
        return new Channel('driver.'.$this->driverId);

        // return collect($this->driverIds)->map(function ($driverId) {
        //     return new Channel('driver.'.$driverId);
        // })->toArray();

    }

    public function broadcastAs(){
        return 'delete_order';
    }

    public function broadcastWith(){
        return [
            'orderId' => $this->orderId
        ];
    }

    /**
     * Get create a new event instance.
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set create a new event instance.
     *
     * @return  self
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }
}
