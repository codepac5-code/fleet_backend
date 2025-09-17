<?php
namespace App\Http\Core\Classes;

use App\Http\Core\Classes\HelperClasses\FromTo;
use App\Http\Core\Const\Structures\TransferStructure;
use App\Models\WalletTransaction;
use App\Models\WalletTransactionGroup;
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
    $fromEntity,
    array $toDetails,
    $totalAmount,
    $groupDescriptionAr = 'تحويل متعدد',
    $groupDescriptionEn = 'Multiple transfer',
    $paymentName = 'محفظة فلييت',
    $paymentNameEn = 'Fleet Wallet',
    $sourceType = 'Fleet-Ride',
    $sourceId = 0,
    $status = 'completed',
    $transactionType = 'transfer'
): FromTo {

    if ($fromEntity->walletBalance < $totalAmount) {
        make_exception(__('messages.insufficient_balance'));
    }

    DB::beginTransaction();

    try {
        $transactionReference = uniqid('TRXGRP_');

        WalletTransactionGroup::create([
            'transaction_reference' => $transactionReference,
            'from_type' => get_class($fromEntity),
            'from_id' => $fromEntity->id,
            'to_type' => null,   
            'to_id' => null,
            'amount' => $totalAmount,
            'balance_before' => $fromEntity->walletBalance,
            'balance_after' => $fromEntity->walletBalance - $totalAmount,
            'description' => $groupDescriptionAr,
            'description_en' => $groupDescriptionEn,
            'paymentName' => $paymentName,
            'paymentName_en' => $paymentNameEn,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'status' => $status,
            'transaction_type' => $transactionType,
        ]);

        $fromEntity->walletBalance -= $totalAmount;
        $fromEntity->save();

        $from_walletBalanceValue = $fromEntity->walletBalance + $totalAmount;

        $lastToEntity = null; 

        foreach ($toDetails as $to) {
            if ($to instanceof TransferStructure) {
                $toEntity = $to->getEntity();

                $amount = $to->getAmount();

                $from_walletBalanceValue -= $amount;


                $toEntity->walletBalance += $amount;
                $toEntity->save();


                WalletTransaction::create([
                    'from_type' => get_class($fromEntity),
                    'from_id' => $fromEntity->id,
                    'to_type' => get_class($toEntity),
                    'to_id' => $toEntity->id,
                    'amount' => $amount,
                    'balance_before' => $from_walletBalanceValue + $amount,
                    'balance_after' => $from_walletBalanceValue,
                    'transaction_reference' => $transactionReference, 
                    'status' => $status,
                    'description' => $to->getDescriptionAr(),
                    'description_en' => $to->getDescriptionEn(),
                    'paymentName' => $paymentName,
                    'paymentName_en' => $paymentNameEn,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'transaction_type' => $transactionType,
                ]);

                $lastToEntity = $toEntity;

            } else {
                throw new InvalidArgumentException("MultiTransfer function : Each item must be an instance of TransferStructure class!.");
            }
        }

        DB::commit();

        return new FromTo($fromEntity, $lastToEntity);

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}




public static function createTransactionGroup(
    $fromEntity,
    $toEntity,
    $totalAmount,
    $description,
    $description_en = '',
    $paymentName = 'محفظة فلييت',
    $paymentName_en = 'Fleet Wallet',
    $sourceType = 'Fleet-Ride',
    $sourceId = 0,
    $status = 'pending',
    $transactionType = null
) {
    $transactionReference = uniqid('TRXGRP_'); 

    $group = WalletTransactionGroup::create([
        'transaction_reference' => $transactionReference,
        'from_type' => get_class($fromEntity),
        'from_id' => $fromEntity->id,
        'to_type' => get_class($toEntity),
        'to_id' => $toEntity->id,
        'amount' => $totalAmount,
        'balance_before' => null, 
        'balance_after' => null,
        'description' => $description,
        'description_en' => $description_en,
        'paymentName' => $paymentName,
        'paymentName_en' => $paymentName_en,
        'source_type' => $sourceType,
        'source_id' => $sourceId,
        'status' => $status,
        'transaction_type' => $transactionType,
    ]);

    return $group;
}


}