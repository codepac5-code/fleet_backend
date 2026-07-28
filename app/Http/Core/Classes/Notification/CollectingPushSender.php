<?php

namespace App\Http\Core\Classes\Notification;

class CollectingPushSender implements PushSender
{
    public array $sent = [];

    public function send(string $token, string $title, string $body, array $data): void
    {
        $this->sent[] = ['token' => $token, 'title' => $title, 'body' => $body, 'data' => $data];
    }

    public function count(): int
    {
        return count($this->sent);
    }
}
