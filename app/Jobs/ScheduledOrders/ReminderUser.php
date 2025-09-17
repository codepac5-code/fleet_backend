<?php
namespace App\Jobs\ScheduledOrders;

use App\Events\ReminderScheduledOrderForUser;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\PrivateNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReminderUser implements ShouldQueue 
{
    use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;
        
    /**
     * Create a new job instance.
     */
    public function __construct(private int $orderId)
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order  = Booking::find($this->orderId);
        event(new ReminderScheduledOrderForUser($order,  $order->userId));

        $user_notification_model = new NotificationModel(
            'تذكير',
            "لديك رحلة تبدأ بعد نصف ساعة من الآن",
            'تذكير',
            "لديك رحلة تبدأ بعد نصف ساعة من الآن",
            "https://fleetapp.net/storage/images/system/notification/wallet/remove_from_wallet_notification.png",
            // AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value        
        );
        User::find($order->userId)->notify(new PrivateNotification($user_notification_model));
    
        info('remindered user:'.$order->userId);
    }
}
