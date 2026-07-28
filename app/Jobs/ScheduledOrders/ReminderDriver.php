<?php

namespace App\Jobs\ScheduledOrders;

use App\Http\Core\Const\Config\WhatsappConfig;
use App\Models\User;
use App\Models\Driver;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use App\Notifications\PrivateNotification;
use Illuminate\Foundation\Queue\Queueable;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Core\Const\Options\AppScreenName;

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
        if($order == null){
            info('Erorr in reminder driver job: not found order '.$this->orderId);
            return;
        }

        $minutes = now()->diffInMinutes($order->scheduled_time, absolute: false);
        $minutes = number_format($minutes);
        $user_notification_model = new NotificationModel(
            'تذكير هام',
            " رحلتك المجدولة ستبدأ بعد {$minutes} دقيقة، يرجى الضغط على زر 'الاستعداد للرحلة' خلال 10 دقائق من استلام هذا الإشعار لضمان جاهزيتك.
             نتمنى لك رحلة موفقة!",
            'Important Reminder',
            "Your scheduled trip will start in {$minutes} minutes. Please click the 'Ready for Trip' button within 10 minutes of receiving this notification to confirm your readiness. Have a safe trip!",
            "https://fleetapp.net/storage/images/system/notification//reminder/alarm-clock-icon.jpg",
            // AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value
        );
        Driver::find($order->driverId)->notify(new PrivateNotification($user_notification_model));

        $this->send_whatsapp_message($order->driverId ,$minutes);
        info('remindered driver:'.$order->driverId);
    }


      public function send_whatsapp_message($driverId , $minutes){

        $user = Driver::find($driverId);
        if($user == null){return;}

        $config = [
            'base_url' => WhatsappConfig::baseUrl(),
            'api_key' => WhatsappConfig::apiKey(),
            'session_id' => WhatsappConfig::sessionId(),
            'phone' => $user->phoneNumber,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])->post($config['base_url'] . '/whatsapp/api/v1/message/text/send', [
                'session_id' => $config['session_id'],
                'receiver' => $user->dialCode . $user->phoneNumber,
                // 'receiver' => '+963' . substr($config['phone'], 1),
                'text' => "
                !تذكير هام
                 رحلتك المجدولة ستبدأ بعد {$minutes} دقيقة، يرجى الضغط على زر 'الاستعداد للرحلة' خلال 10 دقائق من استلام هذا الإشعار لضمان جاهزيتك.
                 نتمنى لك رحلة موفقة!",
            ]);

            if ($response->successful()) {
              return;
            }

            info( 'Failed to send the whatsapp reminder for driver');

        } catch (\Exception $e) {
            info( 'Failed to send the whatsapp reminder for driver');
        }
    }

}
