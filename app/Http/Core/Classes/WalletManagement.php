<?php
namespace App\Http\Core\Classes;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

abstract class WalletManagement
{
    
public static function transfer($fromEntity , $toEntity , $amount , $description , $description_en ='' ): FromTo
{

    if ($fromEntity->walletBalance < $amount) {
        make_exception(__('messages.insufficient_balance'));
    }

    DB::beginTransaction();

    try {
        
        $fromEntity->walletBalance = $fromEntity->walletBalance - $amount;
        $fromEntity->save();
        $toEntity->walletBalance = $toEntity->walletBalance + $amount;
        $toEntity->save();

       
        WalletTransaction::create([
            'from_type' => get_class($fromEntity),
            'from_id' => $fromEntity->id,
            'to_type' => get_class($toEntity),
            'to_id' => $toEntity->id,
            'amount' => $amount,
            'balance_before' => $fromEntity->walletBalance  + $amount,
            'balance_after' => $fromEntity->walletBalance,
            'status' => 'completed',
            'description' => $description,
            'description_en' => $description_en,
            'paymentName' =>'Fleet Wallet',
            'paymentName_en' =>'محفظة فلييت',
            'source_type' => 'Ride',
            'source_id' => 1,
        ]);

        DB::commit();
        return new FromTo($fromEntity , $toEntity );
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}


}