<?php
namespace App\Http\Core\Classes\NotificationsSenderClasses;

use App\Http\Core\Classes\AssetManagement;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Models\NotificationModel;

abstract class TransactionWalletNotifications 
{

    public static function deducted_from_your_wallet_with_dues($deductedAmount , $remainingDues){
        return new NotificationModel(
            'تم خصم مبلغ من محفظتك', //                 __('massages.wallet_deduction_title'),
            'تم خصم '. $deductedAmount.' SYP من محفظتك، ولا يزال لديك مستحقات بقيمة '.$remainingDues.' SYP يجب دفعها في أقرب وقت.',
            'An Amount Has Been Deducted from Your Wallet',
            'An amount of '. $deductedAmount.' SYP has been deducted from your wallet, and you still have dues of '.$remainingDues.' SYP that must be paid as soon as possible.',                // sprintf(  
            //     __("massages.wallet_deduction_message"),
            //     $deductedAmount,
            //     $remainingDues
            // ),
            AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value
        );
    }


    public static function deducted_from_your_wallet($deductedAmount ) : NotificationModel{
       return new NotificationModel(
            'نم خصم العمولة الخاصة بالمكتب',
            sprintf(
                "تم خصم مبلغ قدره %d ل.س من محفظتك كرسوم عمولة.",
                $deductedAmount
            ),
            'Office Commission Deducted',
            sprintf(
                "an amount of %d SYP has been deducted from your wallet as commission fees.",
                $deductedAmount
            ),
            AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value
        );
    }

    public static function user_deducted_from_your_wallet($deductedAmount, $orderId) : NotificationModel {
        return new NotificationModel(
            'تم خصم مبلغ من المحفظة',
            sprintf(
                "تم خصم مبلغ %d ل.س من محفظتك عن الرحلة رقم #%d.",
                $deductedAmount,
                $orderId
            ),
            'Amount Deducted from Wallet',
            sprintf(
                "an amount of %d SYP has been deducted from your wallet for order #%d.",
                $deductedAmount,
                $orderId
            ),
            AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value
        );
    }
    


}