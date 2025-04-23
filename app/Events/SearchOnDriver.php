<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SearchOnDriver
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $orderId;
    private $userId;
    private $latitude;
    private $longitude;
    private $radius;
    private $data;
    // private $sub_service;


    /**
     * Create a new event instance.
     */
    public function __construct( $data)
    {
       // info('event info :' .  $data);

        $this->orderId   = isset($data['orderId'])            ? $data['orderId'] : null;
        $this->radius    = isset($data['radius'])             ? $data['radius']  : 1;
        $this->latitude  = isset($data['startLatitude'])      ? $data['startLatitude']  : null;
        $this->longitude = isset($data['startLongitude'])     ? $data['startLongitude'] : null;
        $this->data      =  $data ;
        // $this->sub_service    =  $data ;

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

    /**
     * Get create a new event instance.
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set create a new event instance.
     *
     * @return  self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * Get create a new event instance.
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set create a new event instance.
     *
     * @return  self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get create a new event instance.
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set create a new event instance.
     *
     * @return  self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of radius
     */ 
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Set the value of radius
     *
     * @return  self
     */ 
    public function setRadius($radius)
    {
        $this->radius = $radius;

        return $this;
    }
}
