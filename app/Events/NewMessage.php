<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $channel;

    public function __construct($channel, $message)
    {
        $this->channel = $channel;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return [$this->channel];
    }


    public function broadcastAs()
    {
        return 'new-message';
    }
}
