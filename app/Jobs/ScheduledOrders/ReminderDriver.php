<?php

namespace App\Jobs\ScheduledOrders;

use App\Events\ReminderScheduledOrderForUser;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\PrivateNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReminderDriver implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct( private int $orderId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order  = Booking::find($this->orderId);
        event(new ReminderScheduledOrderForUser($order,  $order->driverId));

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
        Driver::find($order->driverId)->notify(new PrivateNotification($user_notification_model));
    
        info('remindered driver:'.$order->driverId);
    }
}
