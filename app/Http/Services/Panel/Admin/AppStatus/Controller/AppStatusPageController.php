<?php

namespace App\Http\Services\Panel\Admin\AppStatus\Controller;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\View\View;

class AppStatusPageController extends Controller
{
    public function __invoke(): View
    {
        // App status is platform-wide (global SiteSetting): maintenance mode and
        // version gates apply to every country's apps at once.
        return view('panel.app-status.index', [
            'maintenance' => (bool) SiteSetting::val('app_maintenance', 0),
            'maintenance_message' => (string) SiteSetting::val('app_maintenance_message', ''),
            'android_min' => (string) SiteSetting::val('app_android_min_version', ''),
            'android_latest' => (string) SiteSetting::val('app_android_latest_version', ''),
            'ios_min' => (string) SiteSetting::val('app_ios_min_version', ''),
            'ios_latest' => (string) SiteSetting::val('app_ios_latest_version', ''),
        ]);
    }
}
