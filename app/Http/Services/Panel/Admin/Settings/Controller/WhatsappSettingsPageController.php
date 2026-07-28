<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\View\View;

class WhatsappSettingsPageController extends Controller
{
    public function __invoke(): View
    {
        $token = (string) SiteSetting::val('whatsapp_token', '');

        return view('panel.settings.whatsapp', [
            'baseUrl' => (string) SiteSetting::val('whatsapp_base_url', (string) config('services.whatsapp.base_url', '')),
            'prefix' => (string) SiteSetting::val('whatsapp_prefix', (string) config('services.whatsapp.prefix', 'whatsapp/api/v1')),
            'sessionId' => (string) SiteSetting::val('whatsapp_session_id', ''),
            'tokenHint' => $token !== '' ? '••••' . substr($token, -4) : null,
        ]);
    }
}
