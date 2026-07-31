<?php

namespace App\Jobs;

use App\Http\Core\Classes\Notification\PushSender;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Fans a one-off announcement out to a list of device tokens. The tokens are
 * resolved on the ACTIVE country shard by the composer before dispatch, so this
 * job never crosses country boundaries — it just sends to the strings it's given.
 */
class SendBroadcastPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $tokens,
        private string $title,
        private string $body,
        private ?int $nodeId = null
    ) {
        $this->onQueue('jobs');
    }

    public function handle(PushSender $push): void
    {
        // Re-activate the country shard the composer targeted: the tokens live on
        // that shard's DB, so a dead one is pruned from the right place. The
        // worker runs with no request/shard context of its own.
        if ($this->nodeId !== null) {
            $node = InfrastructureNode::query()->find($this->nodeId);

            if ($node !== null) {
                ShardManager::activate($node);
            }
        }

        foreach ($this->tokens as $token) {
            $token = (string) $token;

            if ($token === '') {
                continue;
            }

            $push->send($token, $this->title, $this->body, ['type' => 'announcement']);
        }
    }
}
