<?php
namespace App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Logic;

use App\Http\Core\Classes\AssetManagement;
use App\Http\Core\Classes\WalletManagement;
use App\Http\Core\Const\Options\AppScreenName;
use App\Http\Core\Const\Options\PaymentMethodType;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Apis\ConfirmPaymentPhoneNumber\Logic\ConfirmPaymentPhoneNumberInput;
use App\Http\Services\Apis\ConfirmPaymentPhoneNumber\Logic\ConfirmPaymentPhoneNumberLogic;
use App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Logic\MTNConfirmPaymentPhoneNumberInput;
use App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Logic\MTNConfirmPaymentPhoneNumberLogic;

class ConfirmPhone_AddBalanceLogic {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function  syriatel ($request) {

    }


    public function  mtn($request) {

        $input = new MTNConfirmPaymentPhoneNumberInput($request);

        $service = new MTNConfirmPaymentPhoneNumberLogic($input); // call the service's logic

        $result = $service->execute();
        $data   = $result->getData();
        
        $invoice = $data['invoice'];

        $user = getAuthUser();
    

        $new_wallet_balance = $user->walletBalance + $invoice->amount; 
        $user = $this->repository->UserRepository()->updateRepository()
        ->update(['id'=> $user->id],['walletBalance' => $new_wallet_balance] );

       // sen notification to user 
        $user_notification_model = new NotificationModel(
            'تم شحن المحفظة فلييت الخاصة بك',
            "تم شحن محفظة فلييت الخاصة بك قيمة {$invoice->amount} من خلال mtn cash فاتورة رقم #{$invoice->id}.",
            'Amount Added to Your Wallet',
            'wallet has been credited with an amount of {$invoice->amount} from mtn cash invoice #{$invoice->id}.',
            AssetManagement::getWalletCreditNotificationImage(),
            true,
            AppScreenName::Wallet_History_Screen->value        
        );

        $this->repository->UserRepository()->readRepository()->notifyUser($user->id , $user_notification_model);


        $description = "تم شحن المحفظة رقم #{$user->id} برصيد {$invoice->amount} من خلال mtn cash فاتورة رقم #{$invoice->id}.";
        $description_en = "Wallet #{$user->id} has been credited with a balance of {$invoice->amount} from mtn cash invoice #{$invoice->id}.";


        // // store transaction 
        // $this->repository->WalletTransactionRepository()->createRepository()->create([
        //     'from_type' => get_class($user),
        //     'from_id' => $user->id,
        //     'to_type' => 'mtn',
        //     'to_id' => $this->repository->PaymentMethodRepository()->readRepository()->getByValue('type','mtn')->id,
        //     'amount' => $invoice->amount,
        //     'balance_before' => $user->walletBalance  + $invoice->amount,
        //     'balance_after' => $user->walletBalance,
        //     'status' => 'completed',
        //     'description' => $description,
        //     'description_en' => $description_en,
        //     'paymentName' =>'MTN',
        //     'paymentName_en' =>'MTN',
        //     'source_type' => 'charge wallet',
        //     'source_id' => $invoice->id,
        // ]);

        $response  = new ConfirmPhone_AddBalanceOutput( $invoice , 
            '!تـم شـحن المحـفظة بنجــاح');
            return $response->send_as_object();
 
    }


   
}