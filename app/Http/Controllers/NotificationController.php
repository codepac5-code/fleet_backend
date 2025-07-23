<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class NotificationController extends Controller
{

    protected function getRedisKey($userId)
    {
        return "user:notifications-2";
    }

    public function getUserAlerts(Request $request)
    {
        $userId = $request->user()->id;
        $key = $this->getRedisKey($userId);

        $notifications = Redis::lrange($key, 0, -1);
        $notifications = array_map(function ($item) {
            return json_decode($item, true);
        }, $notifications);

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function clearUserAlerts(Request $request)
    {
        $userId = $request->user()->id;
        $key = $this->getRedisKey($userId);

        Redis::del($key);

        return response()->json([
            'message' => 'Notifications cleared successfully.'
        ]);
    }
    // protected $redisKey = 'notifications_key';

    // public function getNotifications()
    // {
    //     $data = Redis::get($this->redisKey);

    //     if (!$data) {
    //         return response()->json(['notifications' => []], 200);
    //     }

    //     $notifications = unserialize($data);

    //     return response()->json(['notifications' => $notifications], 200);
    // }

    // public function clearNotifications()
    // {
    //     Redis::del($this->redisKey);

    //     return response()->json(['message' => 'Notifications cleared'], 200);
    // }

    // // تابع مساعد لتخزين إشعار
    // public static function pushNotification($notification)
    // {
    //     $existing = Redis::get('notifications_key');
    //     $notifications = $existing ? unserialize($existing) : [];

    //     $notifications[] = $notification;

    //     Redis::set('notifications_key', serialize($notifications));
    // }
}
