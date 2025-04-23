<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Notifications\Messages\BroadcastMessage;

class PrivateNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use HasUuids;

    
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
        // return ['broadcast'];
       return ['database' , 'broadcast'];
    }



    public function toDatabase($notifiable)
    {
        return [
            'title'     => $this->notificationModel->getTitle(),
            'body'      => $this->notificationModel->getBody(),
            'image'     => $this->notificationModel->getImage(),
            'onClick'   => $this->notificationModel->getOnClick(),
            'type'      => $this->notificationModel->getType(),
        ];
    }



    public function toBroadcast($notifiable)
    {
        return( new BroadcastMessage([
            'title'   => $this->notificationModel->getTitle(),
            'body'    => $this->notificationModel->getBody(),
            // 'onClick' => $this->notificationModel->getOnClick(),
            // 'image'   => $this->notificationModel->getImage()
            // 'send_at' => '2025/1/1',
        ]))
        //  ->onConnection('redis')
         ->onQueue('notifications');
    }


    public function broadcastAs(){
    return 'new_notification';
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
