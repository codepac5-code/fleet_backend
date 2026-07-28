<?php

namespace App\Http\Core\Classes\Notification;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LaravelMailSender implements MailSender
{
    public function send(string $to, string $subject, string $body, array $data): void
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (Throwable $e) {
            Log::warning('mail send failed: ' . $e->getMessage());
        }
    }
}
