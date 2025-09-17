<?php
namespace App\Http\Services\User\Auth\UserCheckOtpService\Logic;

use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UserCheckOtpServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserCheckOtpServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        if (getCatch("user_id_".$this->input->getUserId()) == null) {
            make_exception(__('messages.invalid_otp'),422);
        }

        $userReadRepository = $this->repository->UserRepository()->readRepository();
        $user = $userReadRepository->find($this->input->getUserId());

        if($user == null){
            make_exception(__('messages.something_wrong'),422);
        }

       

        if($this->input->getReferralCode() != null && $this->input->getReferralCode() != ''){

            $reward_user = $this->repository->UserRepository()->
            readRepository()->getByValue('referralCode', $this->input->getReferralCode());

            if( $reward_user == null){
                make_exception(__('messages.invalid_referral_code'));
            }

            $this->sendReferralUsedNotification( $user->id);
            $this->sendReferralRewardNotification( $reward_user->id);
            
        }

        if (!$user->is_registered) {
            $user->is_registered = 1;
            $user->save();
        }
        
        $response  = new UserCheckOtpServiceOutput($user , '');
        return $response->send_as_array();
   }



   public function sendReferralUsedNotification($userId){
    $percentage_discount = 20;
    $expireDate = date('Y-m-d', strtotime('+15 days'));
    $coupon_code = $this->repository->UserRepository()->readRepository()
    ->addCouponToUser($userId, $percentage_discount ,$expireDate ,'WELCOME-' );


   $user_notification_model = new NotificationModel(
    "🎉 مكافأتك لاستخدام كود الإحالة!",
    "لقد حصلت على كوبون خصم بنسبة {$percentage_discount}% كمكافأة لاستخدامك كود الإحالة! استخدم الكود: {$coupon_code} عند الطلب، قبل تاريخ " . date('Y-m-d', strtotime($expireDate)) . " للاستفادة.",
    "🎉 Your Referral Reward!",
    "You’ve received a {$percentage_discount}% discount coupon as a reward for using a referral code! Use the code: {$coupon_code} when placing your order before " . date('Y-m-d', strtotime($expireDate)) . ".",    
    "https://fleetapp.net/storage/images/system/notification/referral_discount_coupon.png",
    false,
    null
    // AppScreenName::Coupons_Screen->value
);
    $this->repository->UserRepository()->readRepository()->notifyUser($userId , $user_notification_model);
   }



   public function sendReferralRewardNotification($userId){
    $referral_reward_percentage = 50;
    $expireDate = date('Y-m-d', strtotime('+15 days'));
    $referrer_coupon_code = $this->repository->UserRepository()->readRepository()
    ->addCouponToUser($userId, $referral_reward_percentage ,$expireDate ,'WELCOME-');

    //  referrer

    $referrer_notification_model = new NotificationModel(
        "🎉 مبروك! لقد حصلت على مكافأة إحالة",
        "شخص ما قام بالتسجيل في التطبيق باستخدام كود الإحالة الخاص بك، وكمكافأة لك، حصلت على كوبون خصم بنسبة {$referral_reward_percentage}%! استخدم الكود: {$referrer_coupon_code} قبل تاريخ {$expireDate} للاستفادة.",
        "🎉 Congratulations! You’ve Earned a Referral Reward",
        "Someone signed up using your referral code! As a thank-you, you’ve received a {$referral_reward_percentage}% discount coupon. Use the code: {$referrer_coupon_code} before {$expireDate} to enjoy your reward.",
        "https://fleetapp.net/storage/images/system/notification/referral_reward_coupon.png",
        false,
        null
        // AppScreenName::Coupons_Screen->value
    );

    $this->repository->UserRepository()->readRepository()->notifyUser($userId , $referrer_notification_model);


   }
}
