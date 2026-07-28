<?php

namespace App\Http\Core\Const\Notification;

class TemplateCatalog
{
    const DEFAULT_LOCALE = 'en';

    const TEMPLATES = [
        'ride_offer_driver' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'New ride offer', 'ar' => 'عرض رحلة جديد'],
            'body' => [
                'en' => 'New ride offer for booking #{booking_id}. Tap to accept before it expires.',
                'ar' => 'عرض رحلة جديد للحجز رقم #{booking_id}. اقبله قبل انتهائه.',
            ],
        ],
        'ride_assigned_driver' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'New ride assigned', 'ar' => 'تم إسناد رحلة جديدة'],
            'body' => [
                'en' => 'You are assigned to booking #{booking_id}. Head to pickup.',
                'ar' => 'تم إسنادك للحجز رقم #{booking_id}. توجّه لنقطة الالتقاط.',
            ],
        ],
        'ride_assigned_rider' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Driver on the way', 'ar' => 'السائق في الطريق'],
            'body' => [
                'en' => 'A driver from your office accepted booking #{booking_id}.',
                'ar' => 'قبل سائق من مكتبك الحجز رقم #{booking_id}.',
            ],
        ],
        'wallet_credited' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Wallet topped up', 'ar' => 'تم شحن المحفظة'],
            'body' => [
                'en' => 'Your wallet was credited with {amount}.',
                'ar' => 'تم إضافة {amount} إلى محفظتك.',
            ],
        ],
        'ride_released_driver' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Ride completed', 'ar' => 'اكتملت الرحلة'],
            'body' => [
                'en' => 'Booking #{booking_id} is complete. Your earnings are in your wallet.',
                'ar' => 'اكتمل الحجز رقم #{booking_id}. أرباحك في محفظتك.',
            ],
        ],
        'ride_released_office' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Ride settled', 'ar' => 'تمّت تسوية الرحلة'],
            'body' => [
                'en' => 'Booking #{booking_id} completed and was settled.',
                'ar' => 'اكتمل الحجز رقم #{booking_id} وتمّت تسويته.',
            ],
        ],
        'chat_message_user' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'New message', 'ar' => 'رسالة جديدة'],
            'body' => [
                'en' => 'New message: {body}',
                'ar' => 'رسالة جديدة: {body}',
            ],
        ],
        'chat_message_office' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'New message from a rider', 'ar' => 'رسالة جديدة من راكب'],
            'body' => [
                'en' => 'A rider sent: {body}',
                'ar' => 'أرسل راكب: {body}',
            ],
        ],
        'payout_paid' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Payout sent', 'ar' => 'تمّ إرسال السحب'],
            'body' => [
                'en' => 'Your payout of {amount} has been processed.',
                'ar' => 'تمّت معالجة سحبك بمبلغ {amount}.',
            ],
        ],
        'rating_received' => [
            'channels' => ['inapp'],
            'subject' => ['en' => 'You received a rating', 'ar' => 'حصلت على تقييم'],
            'body' => [
                'en' => 'You got a {stars}-star rating for booking #{booking_id}.',
                'ar' => 'حصلت على تقييم {stars} نجوم للحجز رقم #{booking_id}.',
            ],
        ],
        'support_reply_office' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Support replied', 'ar' => 'ردّ الدعم'],
            'body' => [
                'en' => 'The support team replied to your ticket #{ticket_id}.',
                'ar' => 'ردّ فريق الدعم على تذكرتك رقم #{ticket_id}.',
            ],
        ],
        'support_reply_user' => [
            'channels' => ['inapp', 'push'],
            'subject' => ['en' => 'Support replied', 'ar' => 'ردّ الدعم'],
            'body' => [
                'en' => 'Support replied to your ticket #{ticket_id}.',
                'ar' => 'ردّ فريق الدعم على تذكرتك رقم #{ticket_id}.',
            ],
        ],
        'overage_invoiced_office' => [
            'channels' => ['inapp', 'email'],
            'subject' => ['en' => 'Overage invoice raised', 'ar' => 'صدرت فاتورة تجاوز'],
            'body' => [
                'en' => 'An overage invoice ({invoice_ref}) for {period} was raised on your account.',
                'ar' => 'صدرت فاتورة تجاوز ({invoice_ref}) عن فترة {period} على حسابك.',
            ],
        ],
    ];

    public static function get(string $key): ?array
    {
        return self::TEMPLATES[$key] ?? null;
    }
}
