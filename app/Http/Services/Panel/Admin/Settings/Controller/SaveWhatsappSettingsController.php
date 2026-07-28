<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveWhatsappSettingsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'whatsapp_base_url' => ['nullable', 'string', 'max:255'],
            'whatsapp_prefix' => ['nullable', 'string', 'max:120'],
            'whatsapp_session_id' => ['nullable', 'string', 'max:255'],
            'whatsapp_token' => ['nullable', 'string', 'max:255'],
        ]);

        SiteSetting::put('whatsapp_base_url', (string) ($data['whatsapp_base_url'] ?? ''));
        SiteSetting::put('whatsapp_prefix', (string) ($data['whatsapp_prefix'] ?? ''));
        SiteSetting::put('whatsapp_session_id', (string) ($data['whatsapp_session_id'] ?? ''));

        // The token is masked in the form: a blank field means "keep the stored
        // value" — only overwrite when the admin actually typed a new one.
        $token = trim((string) ($data['whatsapp_token'] ?? ''));

        if ($token !== '') {
            SiteSetting::put('whatsapp_token', $token);
        }

        if (method_exists(SiteSetting::class, 'flush')) {
            SiteSetting::flush();
        }

        return redirect()
            ->route('panel.admin.settings.whatsapp')
            ->with('status', textByLanguage('تم حفظ إعدادات واتساب', 'WhatsApp settings saved'));
    }
}
