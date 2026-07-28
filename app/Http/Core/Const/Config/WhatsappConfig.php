<?php

namespace App\Http\Core\Const\Config;

/**
 * WhatsApp API connection, sourced from `config('services.whatsapp.*')` (env,
 * overridable from the panel — see AppServiceProvider::applyIntegrationSettingsOverride).
 * Credentials are NOT hardcoded here; manage them under panel Settings → Payments/WhatsApp.
 */
class WhatsappConfig
{
    public static function baseUrl(): string
    {
        return (string) config('services.whatsapp.base_url', '');
    }

    public static function apiKey(): string
    {
        return (string) config('services.whatsapp.token', '');
    }

    public static function sessionId(): string
    {
        return (string) config('services.whatsapp.session_id', '');
    }
}
