<?php

namespace App\Http\Core\Classes\Notification;

interface PushSender
{
    public function send(string $token, string $title, string $body, array $data): void;
}
