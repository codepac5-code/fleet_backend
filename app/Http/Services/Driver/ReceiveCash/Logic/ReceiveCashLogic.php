<?php
namespace App\Http\Services\Driver\ReceiveCash\Logic;

use App\Http\Core\Classes\AssetManagement;
use App\Http\Core\Classes\CommissionManagement;
use App\Http\Core\Classes\NotificationsSenderClasses\TransactionWalletNotifications;
use App\Http\Core\Classes\RedisManager;
use App\Http\Core\Classes\StatisticsEvent;
use App\Http\Core\Classes\WalletManagement;
use App\Http\Core\Const\Options\AppScreenName;
use PHPUnit\Event\Tracer\Tracer;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Options\PaymentType;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;

class ReceiveCashLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ReceiveCashInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        $driver_read_repo = $this->repository->DriverRepository()->readRepository();
        $driver = $driver_read_repo->find($this->input->getDriverId());
        $fleet_office = $this->repository->FleetOfficeRepository()->readRepository()->getFirstByConditions([]);
        $order  = $this->repository->BookingRepository()->readRepository()->find($this->input->getOrderId());


        $fleet_commission   = $order->fleetCommissionValue ;
        $office_commission  = $order->officeCommissionValue;

        beginTransaction();

        if($driver->free_driver && $driver->officeId == null){
            $this->handleFreeDriver($driver, $driver_read_repo ,$fleet_office, $fleet_commission);
            // $this->repository->UserRepository()->readRepository()
            // ->notifyUser($order->userId, TransactionWalletNotifications::user_deducted_from_your_wallet($order->totalAmount, $order->id));
        }

        elseif(!$driver->free_driver && $driver->officeId != null){
            $this-> handleOfficeDriver($driver , $driver_read_repo , $fleet_office, $driver->office , $fleet_commission , $office_commission);
            // $this->repository->UserRepository()->readRepository()
            // ->notifyUser($order->userId, TransactionWalletNotifications::user_deducted_from_your_wallet($order->totalAmount, $order->id));
        }


    // 'officeCommission',
    // 'driverCommission',
    // 'fleetCommission',
    // is free driver ?

    
        // $walletBalance = $driver->walletBalance - $officeDues;

        // $payout = $this->repository->DriverPayoutRepository()->createRepository()
        // ->create([
        //     'driverId' => $driver->id,
        //     'officeId' => $driver->officeId,
        //     'orderId'  => $order->id,
        //     'amount'   => $order->amount,
        //     'description' => 'مستحقات ',
        // ]);

        // if( $payout == null){
        //     rollbackTransaction();
        //     make_exception('حدث خطأ ما يرجى اعادة المحاولة');
        // }

        $booking_updated = $this->repository->BookingRepository()->updateRepository()->update(
            ['id'=>$this->input->getOrderId()],[
                'status'    => OrderStatus::$Completed ,
                'paymentType' => PaymentType::$Cash,
                'paymentStatus'=> 'paid',
                'PaymentDatetime'=>now(),
                'endAt'=>now(),
            ]
        );
    

    if($booking_updated == 0){
        rollbackTransaction();
        make_exception(__('messages.something_wrong'));
    }    

    
    // $statistic = $this->repository->FleetStatisticRepository()->readRepository()->getFirstByConditions([]);
    // $drivers_debt = $statistic->drivers_debt + $office_commission;
    // $updated = $this->repository->FleetStatisticRepository()->updateRepository()
    // ->update(['id'=>$statistic->id] , [ 'drivers_debt'=> $drivers_debt]);

    // if($updated > 0){
    //     $redis_manager = new RedisManager();
    //     // $available_amount    = $redis_manager->add_to_system_avalible_amount($office_commission);
    //     $pending_amount      = RedisManager::add_to_system_pending_amount( -1 * $office_commission );

    // //------- 
    //   StatisticsEvent::Pending_Card
    //   ->send_event_to_admin($pending_amount);
      
    //   StatisticsEvent::OfficeDue
    //   ->send_event_to_admin(
    //     $drivers_debt
    //     );
     
    //     $Completed = $redis_manager->system_move_orders_from_ongoing_to_completed(1);

    //     StatisticsEvent::Ongoing_Ride
    //     ->send_event_to_admin(
    //         $redis_manager->get_system_daily_ongoing_rides()
    //     );
        
    //     StatisticsEvent::Completed_Ride
    //     ->send_event_to_admin(
    //         $Completed
    //       );
    //}
    
      //----------------
        commitTransaction();

        $this->updateRedisDatabase_Order();
        $response  = new ReceiveCashOutput([] , __('messages.amount_received_success'));
        return $response->send_as_object();
   }


   private function handleFreeDriver($driver, $driverRepository ,$fleetOffice, $fleetCommission)
   {
       if ($driver->walletBalance < $fleetCommission && $driver->walletBalance > 1000) {
           $deductedAmount = $driver->walletBalance;
           $remainingDues  = $fleetCommission - $deductedAmount;
           if($remainingDues >= 500){
            WalletManagement::transfer($driver, $fleetOffice, $deductedAmount, '# عمولة الطلب رقم');
            $driverRepository->notifyDriver($driver->id, TransactionWalletNotifications::deducted_from_your_wallet_with_dues($deductedAmount, $remainingDues));
            $driver->fleetDues = $driver->fleetDues + $remainingDues;
            $driver->save();
            return;
           }
       }
       elseif($driver->walletBalance >= $fleetCommission) {
        WalletManagement::transfer($driver, $fleetOffice, $fleetCommission , '# عمولة الطلب رقم');
        $driverRepository->notifyDriver($driver->id, TransactionWalletNotifications::deducted_from_your_wallet($fleetCommission));
       }else{
        $driver->fleetDues  = $driver->fleetDues +  $fleetCommission;
        $driver->save();
       }
   }


   private function handleOfficeDriver($driver,$driverRepository , $fleetOffice, $office , $fleetCommission , $officeCommission)
   {


    
       if ($driver->walletBalance < $fleetCommission && $driver->walletBalance > 1000) {
           $deductedAmount = $driver->walletBalance;
           $remainingDues = $fleetCommission - $deductedAmount;
           if($remainingDues >= 500){
            $description = '# عمولة الطلب رقم';
            $description_en = 'Order Commission No.';
            $transfer = WalletManagement::transfer($driver, $fleetOffice, $deductedAmount,$description , $description_en);
            $driver = $transfer->getFromValue();
            $driver->fleetDues = $driver->fleetDues + $remainingDues;
            $officeDues = $this->calculateOfficeDues($driver, $office, $officeCommission);
            $driver->officeDues  = $driver->officeDues +  $officeDues;
            $driver->save();
            $remainingDues = $officeDues +  $remainingDues;
            $driverRepository->notifyDriver($driver->id, TransactionWalletNotifications::deducted_from_your_wallet_with_dues($deductedAmount, $remainingDues));
            return;
           }
       } 
       elseif($driver->walletBalance >= $fleetCommission) {

        $description = '# عمولة الطلب رقم';
        $description_en = 'Order Commission No.';

        $transfer = WalletManagement::transfer($driver, $office, $fleetCommission, $description , $description_en);
        $driver = $transfer->getFromValue();
        $officeDues = $this->calculateOfficeDues(  $driver , $office, $officeCommission);
        if($officeDues > 0){
            $driver->officeDues  = $driver->officeDues +  $officeDues;
            $driver->save();
            $deductedAmount = $fleetCommission + ( $officeCommission - $officeDues);
            $driverRepository->notifyDriver($driver->id, TransactionWalletNotifications::deducted_from_your_wallet_with_dues($deductedAmount, $officeDues));
            return;
        } 
        $driverRepository->notifyDriver($driver->id, TransactionWalletNotifications::deducted_from_your_wallet($fleetCommission + $officeCommission));

       }
       else {
        $driver->fleetDues = $driver->fleetDues  +  $fleetCommission;
        $driver->officeDues = $driver->fleetDues +  $officeCommission;
        $driver->save();
       }
   }


   private function calculateOfficeDues($driver, $office, $office_commission)
   {

    // wallet balance < from office 
       if ($driver->walletBalance < $office_commission && $driver->walletBalance >= 1000) {
           $deductedAmount = $driver->walletBalance;
           $remainingDues = $office_commission - $deductedAmount;
           if($remainingDues >= 500){
            $description = '# عمولة الطلب رقم';
            $description_en = 'Order Commission No.';

            WalletManagement::transfer($driver, $office, $deductedAmount, $description ,$description_en);
            return $remainingDues;
           }
       } 
       elseif($driver->walletBalance >= $office_commission) {
        $description = '# عمولة الطلب رقم';
        $description_en = 'Order Commission No.';
            WalletManagement::transfer($driver, $office, $office_commission, $description , $description_en);
            return 0;
       }
       else{
        return $office_commission;
       }
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