<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\View\View;

class PaymentSettingsPageController extends Controller
{
    public function __invoke(): View
    {
        $secret = (string) SiteSetting::val('stripe_secret', '');
        $webhook = (string) SiteSetting::val('stripe_webhook_secret', '');

        return view('panel.settings.payments', [
            'publicKey' => (string) SiteSetting::val('stripe_public', ''),
            'secretHint' => $this->hint($secret),
            'webhookHint' => $this->hint($webhook),
        ]);
    }

    private function hint(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        return '••••' . substr($value, -4);
    }
}
