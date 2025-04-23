<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class BroadcastUserNotification extends Notification //implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private NotificationModel $notificationModel)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }



    public function toBroadcast($notifiable)
    {
        return( new BroadcastMessage([
            'title'  => $this->notificationModel->get_title_by_locale_language(),
            'body'   => $this->notificationModel->get_body_by_locale_language(),
            // 'onClick' => $this->notificationModel->getOnClick(),
            'image'   => $this->notificationModel->getImage()
            // 'send_at' => '2025/1/1',
        ]))
        //  ->onConnection('redis')
         ->onQueue('notifications');
    }



public function broadcastAs(){
        return 'new_notification';
    }

public function broadcastOn()
{
    return 'public-notification-user';
}



    
/**
 * Get the notification's database type.
 *
 * @return string
 */
public function databaseType(object $notifiable): string
{
    return 'pubic-notification';
}



    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }


    /**
 * Determine which connections should be used for each notification channel.
 *
 * @return array<string, string>
 */
// public function viaConnections(): array
// {
//     return [
//         'broadcast' => 'redis',
//         'database' => 'sync',
//     ];
// }

// /**
//  * Determine which queues should be used for each notification channel.
//  *
//  * @return array<string, string>
//  */
// public function viaQueues(): array
// {
//     return [
//         'broadcast' => 'broadcastNotification',
//         'database' => 'database-notification',
//     ];
// }





// public function failed(Exception $exception)
// {
//     Log::error('Notification failed', [
//         'user_id' => $this->notifiable->id,
//         'notification_data' => $this->data,
//         'error' => $exception->getMessage(),
//     ]);
// }

    
}
