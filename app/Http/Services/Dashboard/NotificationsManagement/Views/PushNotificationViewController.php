<?php
namespace App\Http\Services\Dashboard\NotificationsManagement\Views;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class PushNotificationViewController extends Controller
{
    public function __invoke(Request $request)
    {
        $pageTitle = (__('messages.pushnotification_settings'));
        // $settings = AppSetting::first();
        $settings = [];
        $services = Service::pluck('title as name', 'id');
        return view('setting.push-notification-setting', compact('settings', 'pageTitle', 'services'))->render();
    }
}
