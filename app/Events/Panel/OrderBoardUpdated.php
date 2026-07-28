<?php

namespace App\Events\Panel;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderBoardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private array $channels,
        private string $action,
        private array $order
    ) {}

    public function broadcastOn(): array
    {
        return array_map(fn ($channel) => new Channel($channel), $this->channels);
    }

    public function broadcastAs(): string
    {
        return 'order-board';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'order'  => $this->order,
        ];
    }
}
