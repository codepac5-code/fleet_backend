<?php
namespace App\Http\Core\Classes;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Http\Core\Const\Structures\TransferStructure;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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
            'paymentName' =>'محفظة فلييت',
            'paymentName_en' =>'Fleet Wallet',
            'source_type' => 'Fleet-Ride',
            'source_id' => 0,
        ]);

        DB::commit();
        return new FromTo($fromEntity , $toEntity );
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

    public static function MultiTransfer(
        $fromEntity ,
        array $toDetails ,
        $totalAmount ): FromTo{
  
        
        if ($fromEntity->walletBalance < $totalAmount) {
        make_exception(__('messages.insufficient_balance'));
    }

    DB::beginTransaction();
    try {

    $from_walletBalanceValue = $fromEntity->walletBalance;
    foreach($toDetails as $to ){
        if ($to instanceof TransferStructure) {
            $toEntity = $to->getEntity();
            $from_walletBalanceValue = $from_walletBalanceValue - $to->getAmount();
            $toEntity->walletBalance = $toEntity->walletBalance + $to->getAmount();
            $toEntity->save();
        
            WalletTransaction::create([
                'from_type' => get_class($fromEntity),
                'from_id' => $fromEntity->id,
                'to_type' => get_class($toEntity),
                'to_id' => $toEntity->id,
                'amount' => $to->getAmount(),
                'balance_before' => $fromEntity->walletBalance  + $to->getAmount(),
                'balance_after' => $fromEntity->walletBalance,
                'status' => 'completed',
                'description' => $to->getDescriptionAr(),
                'description_en' => $to->getDescriptionEn(),
                'paymentName' =>'محفظة فلييت',
                'paymentName_en' =>'Fleet Wallet',
                'source_type' => 'Fleet-Ride',
                'source_id' => 0,
            ]);
        }else{
            throw new InvalidArgumentException("MultiTransfer function : Each item must be an instance of TransferStructure class!.");
        }
    }

        DB::commit();
        $fromEntity->save();
        return new FromTo($fromEntity , $toEntity );
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

}