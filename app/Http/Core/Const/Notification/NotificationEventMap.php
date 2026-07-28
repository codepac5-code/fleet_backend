<?php

namespace App\Http\Core\Const\Notification;

use App\Http\Core\Const\Event\EventType;

class NotificationEventMap
{
    const MAP = [
        EventType::DISPATCH_OFFER_CREATED => [
            'driver' => 'ride_offer_driver',
        ],
        EventType::DISPATCH_RIDE_ASSIGNED => [
            'driver' => 'ride_assigned_driver',
            'user' => 'ride_assigned_rider',
        ],
        EventType::WALLET_CREDITED => [
            'user' => 'wallet_credited',
        ],
        EventType::RIDE_RELEASED => [
            'driver' => 'ride_released_driver',
            'office' => 'ride_released_office',
        ],
        EventType::CHAT_MESSAGE_CREATED => [
            'user' => 'chat_message_user',
            'office' => 'chat_message_office',
        ],
        EventType::WALLET_PAYOUT => [
            'driver' => 'payout_paid',
            'office' => 'payout_paid',
        ],
        EventType::RATING_RECEIVED => [
            'driver' => 'rating_received',
            'user' => 'rating_received',
        ],
        EventType::SUPPORT_MESSAGE_CREATED => [
            'office' => 'support_reply_office',
            'user' => 'support_reply_user',
        ],
        EventType::OVERAGE_INVOICED => [
            'office' => 'overage_invoiced_office',
        ],
    ];

    public static function templateFor(string $eventType, string $recipientType): ?string
    {
        return self::MAP[$eventType][$recipientType] ?? null;
    }
}
