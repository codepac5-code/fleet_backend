<?php
namespace App\Http\Services\User\FleetWalletPayment\Logic;

use App\Http\Core\Classes\AssetManagement;
use App\Http\Core\Classes\CommissionManagement;
use App\Http\Core\Classes\WalletManagement;
use App\Http\Core\Const\Options\AppScreenName;
use App\Log;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Options\PaymentType;
use App\Http\Core\Const\Structures\TransferStructure;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Database\Events\TransactionRolledBack;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\OfficeWallet\OfficeWalletRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Models\Notification;

class FleetWalletPaymentLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private FleetWalletPaymentInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------.------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // make_exception('you don\'t have cash');
        beginTransaction();

        // get order information
       $order =  $this->repository->BookingRepository()->readRepository()->getByValue(
            'id',
            $this->input->getOrderId()
        );

        // get user
        $user = $this->repository->UserRepository()->readRepository()->find($this->input->getUserId());

        if($user == null ){
            rollbackTransaction();
            make_exception(__('messages.something_wrong'));
        }
  

        $driver  = $this->repository->DriverRepository()->readRepository()->find($order->driverId);

        if($driver == null ){
            rollbackTransaction();
            make_exception(__('messages.something_wrong'));
        }

  
         if ($user->walletBalance < $order->totalAmount) {
            rollbackTransaction();
            make_exception(__('messages.insufficient_balance'));
        }

        // $fleet_commission    = CommissionManagement::get_fleet_commission_by_driver($driver);
        // $office_commission   = CommissionManagement::get_office_commission_by_driver($driver);
        $driver_commission    = $order->driverCommissionValue;
        $fleet_office = $this->repository->FleetOfficeRepository()
        ->readRepository()->getFirstByConditions([]);
        $to = [
            // new TransferStructure($driver , $order->driver_commission ,$this->description_ar($user->id , $driver->id,$order->id,$order->totalAmount ),$this->description_en($user->id , $driver->id,$order->id,$order->totalAmount)),

            new TransferStructure($driver , $driver_commission ,$this->description_ar($user->id , $driver->id,$order->id,$order->driverCommissionValue ),$this->description_en($user->id , $driver->id,$order->id,$order->driverCommissionValue)),
            new TransferStructure($fleet_office , $order->fleetCommissionValue,$this->description_ar($user->id , $driver->id,$order->id,$order->fleetCommissionValue ),$this->description_en($user->id , $driver->id,$order->id,$order->fleetCommissionValue) ),
        ];
        // if($order->officeCommissionValue != 0 && $order->officeId !=null){
        //     $office = $order->office;
        //     array_push($to,
        //     new TransferStructure($office , $order->officeCommissionValue,$this->description_ar($user->id , $driver->id,$order->id,$order->officeCommissionValue ),$this->description_en($user->id , $driver->id,$order->id,$order->officeCommissionValue) ),
        //     );
        // }


        WalletManagement::MultiTransfer($user , $to , $order->totalAmount);

        
        $booking_updated = $this->repository->BookingRepository()->updateRepository()->update(
            ['id'=>$this->input->getOrderId()],[
                'status'    => OrderStatus::$Completed ,
                'paymentType' => PaymentType::$FleetWallet,
                'paymentStatus'=> 'paid',
                'PaymentDatetime'=>now(),
                'endAt'=>now(),
            ]
        );

        
    
        $this->updateRedisWalletData($order);

        commitTransaction();

        // notify driver
        $driver_notification_model = new NotificationModel(
            'تم اضافة مبلغ جديد إلى محفظتك',
            "تم إضافة مبلغ قدره {$order->driverCommissionValue} إلى محفظتك (ID: {$driver->id}) مقابل عمولة الطلب رقم {$order->id}.",
            'Amount Added to Your Wallet',
            "An amount of {$order->driverCommissionValue} has been added to your wallet (ID: {$driver->id}) for Order #{$order->id} commission.",
            "https://fleetapp.net/storage/images/system/notification/wallet/add_to_wallet_notification.png",

            // AssetManagement::getWalletCreditNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value        
        );
        


        $this->repository->DriverRepository()->readRepository()->notifyDriver($driver->id , $driver_notification_model );

        // notify user
        $user_notification_model = new NotificationModel(
            'تم خصم مبلغ من محفظتك',
            "تم خصم مبلغ قدره ".getPriceFormat($order->totalAmount, 'ar')."  من محفظتك (ID: {$user->id}) مقابل الطلب رقم {$order->id}.",
            'Amount Deducted from Your Wallet',
            "An amount of ".getPriceFormat($order->totalAmount, 'en')." has been deducted from your wallet (ID: {$user->id}) for Order #{$order->id}.",
            "https://fleetapp.net/storage/images/system/notification/wallet/remove_from_wallet_notification.png",

            // AssetManagement::getWalletDebitNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value        
        );
        $this->repository->UserRepository()->readRepository()->notifyUser($user->id , $user_notification_model);
        $this->updateRedisDatabase_Order();

        $response  = new FleetWalletPaymentOutput([] , __('messages.payment_successful', ['amount' => $order->totalAmount]));
        return $response->send_as_array();
   }


   public function updateRedisDatabase_Order(){
    $order = $this->repository->BookingRepository()->readRepository()
    ->find( 
        $this->input->getOrderId()
    );

    // update order status
    OrderRedisModel::updateStatus($order,
        OrderStatus::$OnGoing,
        OrderStatus::$Completed
    );

   }


   public function updateRedisWalletData($order) {
    FleetWalletRedisModel::moveBalance( BalanceStatus::$Pending ,BalanceStatus::$Available  , $order->fleetCommissionValue );
    if($order->officeId != null && $order->officeCommissionValue > 0  ){
        OfficeWalletRedisModel::moveBalance($order->officeId, BalanceStatus::$Pending ,BalanceStatus::$Available  , $order->fleetCommissionValue);
    }
   }


   public function description_ar($fromId , $toId , $orderId , $amount):string{
   return "تحويل من محفظة رقم #{$fromId} إلى محفظة رقم #{$toId} مقابل عمولة الطلب رقم {$orderId}, المبلغ: ".getPriceFormat($amount , 'ar');

   }

   public function description_en($fromId , $toId , $orderId , $amount):string{
    return "Transfer from Wallet #{$fromId } to Wallet #{$toId} for Order #{$orderId} commission, Amount: ".getPriceFormat($amount , 'en');
   }
}