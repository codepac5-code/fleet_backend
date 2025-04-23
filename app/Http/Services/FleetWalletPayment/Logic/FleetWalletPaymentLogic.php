<?php
namespace App\Http\Services\FleetWalletPayment\Logic;

use App\Http\Core\Classes\AssetManagement;
use App\Http\Core\Classes\CommissionManagement;
use App\Http\Core\Classes\WalletManagement;
use App\Http\Core\Const\Options\AppScreenName;
use App\Log;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Database\Events\TransactionRolledBack;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
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

        $description = "تحويل من محفظة رقم #{$user->id} إلى محفظة رقم #{$driver->id} مقابل عمولة الطلب رقم {$order->id}, المبلغ: {$order->totalAmount} ل.س.";
        $description_en = "Transfer from Wallet #{$user->id} to Wallet #{$driver->id} for Order #{$order->id} commission, Amount: {$order->totalAmount}.";

         if ($user->walletBalance < $order->totalAmount) {
            rollbackTransaction();
            make_exception(__('messages.insufficient_balance'));
        }

        // $fleet_commission    = CommissionManagement::get_fleet_commission_by_driver($driver);
        // $office_commission   = CommissionManagement::get_office_commission_by_driver($driver);
        $driver_commission    = $order->driverCommissionValue;


        WalletManagement::transfer($user , $driver , ($driver_commission),
        $description ,$description_en);


        
        $booking_updated = $this->repository->BookingRepository()->updateRepository()->update(
            ['id'=>$this->input->getOrderId()],[
                'status'    => OrderStatus::$Completed ,
            ]
        );

        commitTransaction();

        // notify driver
        $driver_notification_model = new NotificationModel(
            'تم اضافة مبلغ جديد إلى محفظتك',
            "تم إضافة مبلغ قدره {$order->driverCommissionValue} ل.س إلى محفظتك (ID: {$driver->id}) مقابل عمولة الطلب رقم {$order->id}.",
            'Amount Added to Your Wallet',
            "An amount of {$order->totalAmount} SYP has been added to your wallet (ID: {$driver->id}) for Order #{$order->id} commission.",
            AssetManagement::getWalletCreditNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value        
        );
        


        $this->repository->DriverRepository()->readRepository()->notifyDriver($driver->id , $driver_notification_model );

        // notify user
        $user_notification_model = new NotificationModel(
            'تم خصم مبلغ من محفظتك',
            "تم خصم مبلغ قدره {$order->totalAmount} ل.س من محفظتك (ID: {$user->id}) مقابل الطلب رقم {$order->id}.",
            'Amount Deducted from Your Wallet',
            "An amount of {$order->totalAmount} SYP has been deducted from your wallet (ID: {$user->id}) for Order #{$order->id}.",
            AssetManagement::getWalletDebitNotificationImage(),
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
}