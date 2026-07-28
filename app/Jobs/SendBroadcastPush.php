<?php

namespace App\Jobs;

use App\Http\Core\Classes\Notification\PushSender;
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
        private string $body
    ) {
        $this->onQueue('jobs');
    }

    public function handle(PushSender $push): void
    {
        foreach ($this->tokens as $token) {
            $token = (string) $token;

            if ($token === '') {
                continue;
            }

            $push->send($token, $this->title, $this->body, ['type' => 'announcement']);
        }
    }
}
