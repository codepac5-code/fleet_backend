<?php
namespace App\Http\Services\User\GetWalletHistory\Logic;
use App\Models\User;
use App\Models\Driver;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetWalletHistoryLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetWalletHistoryInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {
     
        $user = getAuthUser();

        $transactions = $this->repository->WalletTransactionRepository()->readRepository()
        ->getUserWalletTransactions_paginate($user , 8);
        
        $data  ['walletBalance'] = $user->walletBalance;
        $data  ['transactions']  = $transactions ;




        $response  = new GetWalletHistoryOutput($data , 'get user wallet history');
        return $response->send_as_object();
   }
}







// public function transfer($fromEntity, $toEntity, $amount)
// {
//     $fromWallet = $fromEntity->wallet;
//     $toWallet = $toEntity->wallet;

//     if (!$fromWallet || !$toWallet) {
//         throw new \Exception('Either sender or receiver does not have a wallet.');
//     }

//     if ($fromWallet->balance < $amount) {
//         throw new \Exception('Insufficient balance.');
//     }

//     DB::beginTransaction();

//     try {
//         $fromWallet->update(['balance' => $fromWallet->balance - $amount]);
//         $toWallet->update(['balance' => $toWallet->balance + $amount]);

//        
//         WalletTransaction::create([
//             'from_type' => get_class($fromEntity),
//             'from_id' => $fromEntity->id,
//             'to_type' => get_class($toEntity),
//             'to_id' => $toEntity->id,
//             'amount' => $amount,
//             'balance_before' => $fromWallet->balance,
//             'balance_after' => $fromWallet->balance - $amount,
//             'status' => 'completed',
//         ]);

//         DB::commit();
//     } catch (\Exception $e) {
//         DB::rollBack();
//         throw $e;
//     }
// }





// $sentTransactions = $user->sentTransactions;
// $receivedTransactions = $user->receivedTransactions;


// // all
// $allTransactions = WalletTransaction::where(function ($query) use ($user) {
//     $query->where('from_type', get_class($user))
//           ->where('from_id', $user->id);
// })->orWhere(function ($query) use ($user) {
//     $query->where('to_type', get_class($user))
//           ->where('to_id', $user->id);
// })->get();



// $sentTransactions = $user->sentTransactions()->orderBy('created_at', 'desc')->get();
// $receivedTransactions = $user->receivedTransactions()->orderBy('created_at', 'desc')->get();
// $allTransactions = WalletTransaction::where(function ($query) use ($user) {
//     $query->where('from_type', get_class($user))
//           ->where('from_id', $user->id);
// })->orWhere(function ($query) use ($user) {
//     $query->where('to_type', get_class($user))
//           ->where('to_id', $user->id);
// })->orderBy('created_at', 'desc')->get();




// $user = User::find(1);

// $transactions = WalletTransaction::select(
//     'id',
//     'from_type',
//     'from_id',
//     'to_type',
//     'to_id',
//     'amount',
//     'status',
//     'created_at',
//     DB::raw("IF(from_type = '" . addslashes(get_class($user)) . "' AND from_id = {$user->id}, true, false) as isWithdraw")
// )
//     ->where(function ($query) use ($user) {
//         $query->where(function ($q) use ($user) {
//             $q->where('from_type', get_class($user))
//                 ->where('from_id', $user->id); 
//         })
//         ->orWhere(function ($q) use ($user) {
//             $q->where('to_type', get_class($user))
//                 ->where('to_id', $user->id); 
//         });
//     })
//     ->orderBy('created_at', 'desc') 
//     ->paginate(10);

// return response()->json($transactions);