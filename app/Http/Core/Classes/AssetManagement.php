<?php
namespace App\Http\Core\Classes;



abstract class  AssetManagement
{


    public static function getWalletCreditNotificationImage()
    {
        return "/storage/images/system/notification/wallet/add_to_wallet_notification.png";
    }
    
    public static function getWalletDebitNotificationImage()
    {
        return "/storage/images/system/notification/wallet/remove_from_wallet_notification.png";
    }
    
}


// {"title":"تقييم جديد","body":"تم تقييمك بأربع نجوم عن الرحلة رقم 51599","image":"https://static.vecteezy.com/system/resources/thumbnails/000/456/675/small_2x/6129-gold_star.jpg"}