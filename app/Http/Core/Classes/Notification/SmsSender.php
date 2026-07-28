<?php

namespace App\Http\Core\Classes\Notification;

use Illuminate\Support\Facades\Http;
use Throwable;

class SmsSender
{
    public function send(string $phone, string $message, string $lang = 'ar'): bool
    {
        $username = config('services.smsala.username');
        $password = config('services.smsala.password');
        $apiId = config('services.smsala.api_id');

        if (! $username || ! $password || ! $apiId) {
            return false;
        }

        try {
            return Http::get('https://smsala.com/api/v1/send', [
                'username' => $username,
                'password' => $password,
                'api_id' => $apiId,
                'to' => $phone,
                'msg' => $message,
                'type' => 'text',
                'lang' => $lang,
            ])->successful();
        } catch (Throwable $e) {
            return false;
        }
    }
}
