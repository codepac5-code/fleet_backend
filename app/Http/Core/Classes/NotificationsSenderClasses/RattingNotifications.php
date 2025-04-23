<?php
namespace App\Http\Core\Classes\NotificationsSenderClasses;

use App\Http\Core\Classes\AssetManagement;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;

abstract class RattingNotifications 
{
    public static function new_review_notification($rating, $orderId)
    {
        return new NotificationModel(
            'تقييم جديد',
            'تم تقييمك بـ ' . $rating . ' نجوم عن الرحلة رقم ' . $orderId, 
            'New Review', 
            'You have been rated ' . $rating . ' stars for order number #' . $orderId, 
            'https://static.vecteezy.com/system/resources/thumbnails/000/456/675/small_2x/6129-gold_star.jpg', // صورة الإشعار
            false,
            null//AppScreenName::Review_Screen->value 
        );
    }
    
    


}