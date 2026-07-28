<?php

namespace App\Http\Core;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class WhatsappMessageService
{
    private string $baseUrl;
    private string $apiKey;
    private string $sessionId;

    public function __construct(string $apiKey = null, string $sessionId = null, string $baseUrl = null)
    {
        $this->baseUrl   = $baseUrl ?? (string) config('services.whatsapp.base_url', '');
        $this->apiKey    = $apiKey ?? (string) config('services.whatsapp.token', '');
        $this->sessionId = $sessionId ?? (string) config('services.whatsapp.session_id', '');
    }

    public function send(string $phoneNumber, string $dialCode, string $message): bool
    {
        $payload = $this->preparePayload($phoneNumber, $dialCode, $message);
        $response = $this->post('/whatsapp/api/v1/message/text/send', $payload);

        return $this->handleResponse($response);
    }


    private function preparePayload(string $phoneNumber, string $dialCode, string $message): array
    {
        return [
            'session_id' => $this->sessionId,
            'receiver'   => $dialCode . $phoneNumber,
            'text'       => $message,
        ];
    }


    private function post(string $endpoint, array $data): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
        ])->post($this->baseUrl . $endpoint, $data);
    }


    private function handleResponse(Response $response): bool
    {
        if (!$response->successful()) {
            // logger()->error('Whatsapp send failed', ['response' => $response->body()]);
            return false;
        }

        return true;
    }
}
