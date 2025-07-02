<?php
namespace App\Http\Services\Dashboard\AddBalance\Logic;

use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class AddBalanceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AddBalanceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {




        switch($this->input->getUserType()){

            // add blance to user
            case 'user': $user = $this->repository->UserRepository()
            ->readRepository()->getByValue('id' , $this->input->getUserId());

            $new_balance = $user->walletBalance + $this->input->getAmount();
            $updated_user = $this->repository->UserRepository()
            ->updateRepository()->update(['id'=>$this->input->getUserId()],
            ['walletBalance'=>$new_balance]);

            if(!($updated_user >0)){
                return response()->json(['success'=>false ,'message'=>__('messages.something_wrong'),'walletBalance'=>$new_balance]);
            }
                $this->repository->UserRepository()->readRepository()->notifyUser($user->id , $this->getNotificationModel($this->input->getAmount()));


            break;


            // add blance to driver
            case 'driver': $driver = $this->repository->DriverRepository()
            ->readRepository()->getByValue('id' , $this->input->getUserId());

            $dues = $driver->fleetDues  + $driver->officeDues ; 

            if($dues > 0  && $this->input->getAmount() < $dues ){
                return response()->json(['success'=>false , 'message' => __('messages.please_charge_wallet', ['amount' =>getPriceFormat($dues) ])]);
            }

            if($dues > 0){
                $amount = max($this->input->getAmount() - $dues,0);
                $new_balance = $driver->walletBalance + $amount;
                $dues = 0;

            }else
            {
                $new_balance = $driver->walletBalance + $this->input->getAmount();
            }



            $updated_driver = $this->repository->DriverRepository()
            ->updateRepository()->update(['id'=>$this->input->getUserId()],
            [
                'walletBalance'=>$new_balance,
                'officeDues' => 0,
                'fleetDues' => 0,
        
        ]);

            if(!($updated_driver >0)){
                return response()->json(['success'=>false ,'message'=>__('messages.something_wrong'),'walletBalance'=>$new_balance ,'dues'=> $dues]);
            }
           $this->repository->DriverRepository()->readRepository()->notifyDriver($driver->id , $this->getNotificationModel($this->input->getAmount()));

            break;
        }


        return response()->json(['success'=>true ,'message'=>'updated successfully','walletBalance'=>$new_balance ]);

   }


   private function getNotificationModel($new_balance){
    return new NotificationModel(
        'تم شحن محفظة فلييت',
        "تم شحن محفظة فلييت الخاصة بك بقيمة ".getPriceFormat($new_balance, 'ar').".",
        'Fleet Wallet Credited',
        "Your Fleet wallet has been credited with an amount of ".getPriceFormat($new_balance, 'en').".",
        "https://fleetapp.net/storage/images/system/notification/wallet/remove_from_wallet_notification.png",
    
        // AssetManagement::getWalletDebitNotificationImage(),
        true,
        AppScreenName::Wallet_History_Screen->value        
    );

   }
}