<?php

namespace App\Http\Core\Classes\Notification;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeadMailer
{
    public static function notify(string $subject, array $fields): void
    {
        try {
            $to = env('LEADS_NOTIFY_EMAIL', config('mail.from.address'));

            if (!$to) {
                return;
            }

            $body = '';
            foreach ($fields as $label => $value) {
                $body .= $label . ': ' . ($value === null || $value === '' ? '-' : $value) . "\n";
            }

            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (Throwable $e) {
            Log::warning('lead mail failed: ' . $e->getMessage());
        }
    }
}
