<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavePaymentSettingsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stripe_public' => ['nullable', 'string', 'max:255'],
            'stripe_secret' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
        ]);

        // The publishable key is not secret, so a blank submission clears it.
        SiteSetting::put('stripe_public', (string) ($data['stripe_public'] ?? ''));

        // Secrets are masked in the form, so a blank field means "keep the
        // stored value" — only overwrite when the admin actually typed a new one.
        foreach (['stripe_secret', 'stripe_webhook_secret'] as $key) {
            $value = trim((string) ($data[$key] ?? ''));

            if ($value !== '') {
                SiteSetting::put($key, $value);
            }
        }

        if (method_exists(SiteSetting::class, 'flush')) {
            SiteSetting::flush();
        }

        return redirect()
            ->route('panel.admin.settings.payments')
            ->with('status', textByLanguage('تم حفظ إعدادات الدفع', 'Payment settings saved'));
    }
}
