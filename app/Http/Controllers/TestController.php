<?php

namespace App\Http\Controllers;

use App\Events\ReminderScheduledOrderForUser;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;
use App\Jobs\GeneralPurposeJob;
use App\Jobs\ScheduledOrders\ReminderUser;
use Illuminate\Http\Request;

class testController extends Controller
{
    public function test(){

        GeneralPurposeJob::dispatch(self::class ,'set_reminder_for_user_and_driver',[
            $order->id
            //$order_data['scheduled_time'    
            ])->delay($runAt);            info('wait... nnnn');
    }


   public function set_reminder_for_user_and_driver( $orderId  ){

        $order  = Booking::with(['subService','driver','payment'])->find($orderId);
        
        if($order->status == OrderStatus::$Pending && $order->driverId != null){
            $order->status = OrderStatus::$InProgress;
            $order->save();
            // driver
    
            event(new ReminderScheduledOrderForDriver($order, $order->driverId));
    
            $driver_notification_model = new NotificationModel(
                'تذكير',
                "لديك رحلة تبدأ بعد نصف ساعة من الآن",
                'تذكير',
                "لديك رحلة تبدأ بعد نصف ساعة من الآن",
                "https://fleetapp.net/storage/images/system/notification/wallet/add_to_wallet_notification.png",
    
                // AssetManagement::getWalletCreditNotificationImage(),
                true,
                AppScreenName::Wallet_History_Screen->value        
            );
            
            $this->repository->DriverRepository()->readRepository()->notifyDriver( $order->driverId , $driver_notification_model );
    
    
            //user
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
            $this->repository->UserRepository()->readRepository()->notifyUser( $order->userId , $user_notification_model);
    
        }
    }
}