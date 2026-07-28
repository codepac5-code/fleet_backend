<?php

namespace App\Http\Services\Panel\Admin\AppStatus\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveAppStatusController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'maintenance' => ['nullable', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'android_min' => ['nullable', 'string', 'max:20'],
            'android_latest' => ['nullable', 'string', 'max:20'],
            'ios_min' => ['nullable', 'string', 'max:20'],
            'ios_latest' => ['nullable', 'string', 'max:20'],
        ]);

        SiteSetting::put('app_maintenance', $request->boolean('maintenance') ? '1' : '0');
        SiteSetting::put('app_maintenance_message', (string) ($data['maintenance_message'] ?? ''));
        SiteSetting::put('app_android_min_version', (string) ($data['android_min'] ?? ''));
        SiteSetting::put('app_android_latest_version', (string) ($data['android_latest'] ?? ''));
        SiteSetting::put('app_ios_min_version', (string) ($data['ios_min'] ?? ''));
        SiteSetting::put('app_ios_latest_version', (string) ($data['ios_latest'] ?? ''));

        if (method_exists(SiteSetting::class, 'flush')) {
            SiteSetting::flush();
        }

        return redirect()
            ->route('panel.admin.app-status.index')
            ->with('status', textByLanguage('تم حفظ حالة التطبيق', 'App status saved'));
    }
}
