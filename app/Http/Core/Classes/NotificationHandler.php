<?php
namespace App\Http\Core\Classes;

use App\Http\Core\Models\NotificationModel;
use App\Models\Driver;
use App\Models\Office;
use App\Models\User;
use App\Notifications\BroadcastDriverNotification;
use App\Notifications\BroadcastOfficeNotification;
use App\Notifications\BroadcastUserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

class NotificationHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function send_notification_to_users(NotificationModel $notificationModel) : void {
        //  User::select('id')
        // ->chunk(500, function ($users) use ($notificationModel) {
                try {
                    $user = User::first();
                    Notification::sendNow($user, new BroadcastUserNotification($notificationModel));
                } catch (\Exception $e) {
                    Log::error('Chunk failed to send notifications to users', ['error' => $e->getMessage()]);
                }
            // });
    }



    public function send_notification_to_drivers(NotificationModel $notificationModel) : void {
        //  Driver::select('id')
        // ->chunk(500, function ($users) use ($notificationModel) {
                try {
                    $driver = Driver::first();
                    // $driver = new Driver();
                    Notification::sendNow($driver, new BroadcastDriverNotification($notificationModel));
                } catch (\Exception $e) {
                    Log::error('Chunk failed to send notifications to users', ['error' => $e->getMessage()]);
                }
            // });
    }

    public function send_notification_to_offices(NotificationModel $notificationModel) : void {
    //     Office::select('id')
    //    ->chunk(500, function ($users) use ($notificationModel) {
               try {
                     $office = Office::first();
                   Notification::sendNow($office, new BroadcastOfficeNotification($notificationModel));
               } catch (\Exception $e) {
                   Log::error('Chunk failed to send notifications to users', ['error' => $e->getMessage()]);
               }
        //    });
   }


}