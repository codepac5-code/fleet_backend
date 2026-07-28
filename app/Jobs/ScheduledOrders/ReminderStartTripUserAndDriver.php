<?php

namespace App\Jobs\ScheduledOrders;

use App\Http\Core\Const\Config\WhatsappConfig;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use App\Notifications\PrivateNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ReminderStartTripUserAndDriver implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private int $orderId;


    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }


    public function handle(): void
    {
        $order = Booking::find($this->orderId);
        if ($order === null) {
            info('Error in ReminderStartTripUserAndDriver job: Booking not found for order ID ' . $this->orderId);
            return;
        }

        $this->notifyUser($order->userId);

        $this->notifyDriver($order->driverId);

        info('Trip start notification sent to user and driver for order ID: ' . $this->orderId);
    }

    /**
     * user notification
     */
    private function notifyUser(int $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $title_ar = 'تنبيه هام';
        $body_ar = " رحلتك المجدولة قد بدأت الآن. نتمنى لك رحلة موفقة!";
        $title_en = 'Important Alert';
        $body_en = " Your scheduled trip has started now. Have a safe trip!";

        $user_notification_model = new NotificationModel(
            $title_ar,
            $body_ar,
            $title_en,
            $body_en,
            "https://fleetapp.net/storage/images/system/notification/wallet/remove_from_wallet_notification.png",
            true,
            AppScreenName::Wallet_History_Screen->value
        );
        $user->notify(new PrivateNotification($user_notification_model));
        $this->sendWhatsappMessage($user->dialCode . $user->phoneNumber, 'user');
    }

    /**
     * driver notification
     */
    private function notifyDriver(int $driverId): void
    {
        $driver = Driver::find($driverId);
        if ($driver === null) {
            return;
        }

        $title_ar = 'تنبيه هام';
        $body_ar = "رحلتك المجدولة قد بدأت الآن. يرجى التأكد من جاهزيتك للانطلاق.";
        $title_en = 'Important Alert';
        $body_en = "Your scheduled trip has started now. Please ensure you are ready to start the trip.";

        $driver_notification_model = new NotificationModel(
            $title_ar,
            $body_ar,
            $title_en,
            $body_en,
            "https://fleetapp.net/storage/images/system/notification/wallet/remove_from_wallet_notification.png",
            true,
            AppScreenName::Wallet_History_Screen->value
        );
        $driver->notify(new PrivateNotification($driver_notification_model));

        $this->sendWhatsappMessage($driver->dialCode . $driver->phoneNumber, 'driver');
    }

    /**
     * WhatsApp.
     */
    private function sendWhatsappMessage(string $phoneNumber, string $recipientType): void
    {
        $config = [
            'base_url' => WhatsappConfig::baseUrl(),
            'api_key' => WhatsappConfig::apiKey(),
            'session_id' => WhatsappConfig::sessionId(),
        ];


        $text = $recipientType === 'user'
            ? "Reminder: Your scheduled trip has started now. Have a safe trip!"
            : "Reminder: Your scheduled trip has started now. Please ensure you are ready to start the trip.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])->post($config['base_url'] . '/whatsapp/api/v1/message/text/send', [
                'session_id' => $config['session_id'],
                'receiver' => $phoneNumber,
                'text' => $text,
            ]);

            if (!$response->successful()) {
                info('Failed to send WhatsApp notification to ' . $recipientType);
            }
        } catch (\Exception $e) {
            info('Error sending WhatsApp notification to ' . $recipientType . ': ' . $e->getMessage());
        }
    }
}
