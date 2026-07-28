<?php

namespace App\Http\Core\Classes\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsappSender extends SmsSender
{
    public function send(string $phone, string $message, string $lang = 'ar'): bool
    {
        $base = rtrim((string) config('services.whatsapp.base_url'), '/');
        $prefix = trim((string) config('services.whatsapp.prefix', 'whatsapp/api/v1'), '/');
        $token = (string) config('services.whatsapp.token');
        $session = (string) config('services.whatsapp.session_id');

        if ($base === '' || $token === '' || $session === '') {
            return false;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->post("{$base}/{$prefix}/message/text/send", [
                    'session_id' => $session,
                    'receiver' => $phone,
                    'text' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp OTP delivery failed', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning('WhatsApp OTP delivery error: ' . $e->getMessage());

            return false;
        }
    }
}
