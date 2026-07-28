<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

/**
 * Public app-status probe the mobile apps poll on launch: maintenance mode and
 * per-platform version gates. Global (platform-wide). The apps decide what to do
 * with it (show a maintenance screen, force/soft update) — this only reports.
 */
class PublicAppStatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'maintenance' => (bool) SiteSetting::val('app_maintenance', 0),
            'maintenance_message' => (string) SiteSetting::val('app_maintenance_message', ''),
            'android' => [
                'min_version' => (string) SiteSetting::val('app_android_min_version', ''),
                'latest_version' => (string) SiteSetting::val('app_android_latest_version', ''),
            ],
            'ios' => [
                'min_version' => (string) SiteSetting::val('app_ios_min_version', ''),
                'latest_version' => (string) SiteSetting::val('app_ios_latest_version', ''),
            ],
        ]);
    }
}
