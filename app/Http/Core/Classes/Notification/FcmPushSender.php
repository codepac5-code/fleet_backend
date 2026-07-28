<?php

namespace App\Http\Core\Classes\Notification;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmPushSender implements PushSender
{
    public function send(string $token, string $title, string $body, array $data): void
    {
        try {
            $config = $this->config();

            if ($config === null) {
                return;
            }

            $this->dispatch($config['project_id'], self::buildMessage($token, $title, $body, $data));
        } catch (Throwable $e) {
            Log::warning('fcm push failed: ' . $e->getMessage());
        }
    }

    public static function buildMessage(string $token, string $title, string $body, array $data): array
    {
        return [
            'message' => [
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => self::stringify($data),
            ],
        ];
    }

    private function config(): ?array
    {
        $setting = Setting::query()->where('type', 'OTHER_SETTING')->first();

        if ($setting === null) {
            return null;
        }

        $other = json_decode($setting->value);

        if ($other === null || empty($other->project_id)) {
            return null;
        }

        if (isset($other->firebase_notification) && (int) $other->firebase_notification !== 1) {
            return null;
        }

        return ['project_id' => $other->project_id];
    }

    private function dispatch(string $projectId, array $message): void
    {
        $accessToken = getAccessToken();

        $ch = curl_init('https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        // Fail fast: FCM is a best-effort side-channel. Without a timeout a slow or
        // unreachable Firebase (common from a dev box) blocks the outbox relay for
        // ~10s PER event, so offer events reach the realtime layer long after their
        // TTL and never surface to the driver. The realtime (Redis) publish already
        // ran before this; push must never throttle it.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);

        $response = curl_exec($ch);
        Log::info('fcm response: ' . $response);
        curl_close($ch);
    }

    private static function stringify(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, '_')) {
                continue;
            }

            $out[(string) $key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return $out;
    }
}
