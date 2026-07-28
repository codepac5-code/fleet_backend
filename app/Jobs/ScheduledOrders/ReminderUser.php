<?php
namespace App\Jobs\ScheduledOrders;

use App\Http\Core\Const\Config\WhatsappConfig;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\PrivateNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

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
        if($order == null){
            info('Erorr in reminder user job : not found order '.$this->orderId);
            return;
        }
        $minutes = now()->diffInMinutes($order->scheduled_time, absolute: false);
        $minutes = number_format($minutes);
        $user_notification_model = new NotificationModel(
            'تذكير هام',
            "رحلتك المجدولة ستبدأ بعد {$minutes} دقيقة، يرجى التأكد من استعدادك والانطلاق في الوقت المحدد.
             نتمنى لك رحلة موفقة!",
            'Important Reminder',
            "Your scheduled trip will start in {$minutes} minutes. Please make sure you are ready and on time. Have a safe trip!",
            "https://fleetapp.net/storage/images/system/notification//reminder/alarm-clock-icon.jpg",
            // AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value
        );
        User::find($order->userId)->notify(new PrivateNotification($user_notification_model));

        $scheduledTime = Carbon::parse($order->scheduled_time);

        ReminderStartTripUserAndDriver::dispatch($order->id)
        ->delay($scheduledTime);

        $this->send_whatsapp_message($order->userId ,$minutes);
        info('remindered user:'.$order->userId);
    }


    public function send_whatsapp_message($userId , $minutes ){

        $user = User::find($userId);
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

           info( 'Failed to send the whatsapp reminder');

        } catch (\Exception $e) {
           info('otp error');
        }
    }
}
