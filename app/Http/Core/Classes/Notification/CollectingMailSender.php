<?php

namespace App\Http\Core\Classes\Notification;

class CollectingMailSender implements MailSender
{
    public array $sent = [];

    public function send(string $to, string $subject, string $body, array $data): void
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject, 'body' => $body, 'data' => $data];
    }

    public function count(): int
    {
        return count($this->sent);
    }
}
