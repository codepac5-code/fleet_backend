<?php

namespace App\Http\Core\Classes\Notification;

interface MailSender
{
    public function send(string $to, string $subject, string $body, array $data): void;
}
