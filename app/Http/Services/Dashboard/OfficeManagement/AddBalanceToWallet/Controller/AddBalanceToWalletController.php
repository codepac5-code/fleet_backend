<?php
namespace App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Controller;

use App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Logic\AddBalanceToWalletInput;
use App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Logic\AddBalanceToWalletLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\OfficeManagement\AddBalanceToWallet\Request\AddBalanceToWalletRequest;
use App\Models\Office;

class AddBalanceToWalletController extends Controller
{
    public function __invoke(AddBalanceToWalletRequest $request)
    {

    if ($request->isMethod('get')) {



            $office_id = $request->query('office_id');
            $office = Office::find($office_id);

            if(!$office){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred')
                ]);
            }

            return response()->json([
                'success' => true,
                'balance' => $office->walletBalance ?? 0
            ]);
        }

        if ($request->isMethod('post')) {
            

            $office_id = $request->input('office_id');
            $amount    = $request->input('amount');

            $office = Office::find($office_id);
            if(!$office){
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error_occurred')
                ]);
            }

            $office->walletBalance = ($office->walletBalance ?? 0) + $amount;
            $office->save();

            return response()->json([
                'success' => true,
                'balance' => $office->walletBalance,
                'message' => __('messages.balance_added_success')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.error_occurred')
        ]);



        // validate input data and pass it to the service..
        $input = new AddBalanceToWalletInput($request->validated());

        $service = new AddBalanceToWalletLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
