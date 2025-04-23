<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = 'wallet_transactions';
    protected $fillable = [
        'from', 'to', 'amount',
            'from_type' ,
            'from_id',
            'to_type',
            'to_id' ,
            'amount',
            'balance_before',
            'balance_after',
            'status',
            'source_type',
            'source_id',
            'description',
            'transaction_reference',
            'paymentName',
            'description_en',
            'paymentName_en'
    ];


    public static function SelectWithTranslate(){

        switch(app()->getLocale())
        {
            case 'ar': 
                return WalletTransaction::select([
                    'from', 'to', 'amount',
                    'from_type' ,
                    'from_id',
                    'to_type',
                    'to_id' ,
                    'amount',
                    'balance_before',
                    'balance_after',
                    'status',
                    'source_type',
                    'source_id',
                    'description',
                    'transaction_reference',
                    'paymentName',
                ]);
          
            case 'en': 
                return WalletTransaction::select([
                    'from', 'to', 'amount',
                    'from_type' ,
                    'from_id',
                    'to_type',
                    'to_id' ,
                    'amount',
                    'balance_before',
                    'balance_after',
                    'status',
                    'source_type',
                    'source_id',
                    'transaction_reference',
                    'paymentName',
                    'description_en as description'
                ]);

            default :
            return WalletTransaction::select([
                'from', 'to', 'amount',
                'from_type' ,
                'from_id',
                'to_type',
                'to_id' ,
                'amount',
                'balance_before',
                'balance_after',
                'status',
                'source_type',
                'source_id',
                'description',
                'transaction_reference',
                'paymentName',
                'description_en'
              ]);
        }
     

    }
    // $table->unsignedBigInteger('from_wallet_id'); 
    // $table->unsignedBigInteger('to_wallet_id'); 
    // $table->morphs('from');
    // $table->morphs('to');
    // $table->double('amount'); 
    // $table->double('balance_before')->nullable(); 
    // $table->double('balance_after')->nullable(); 
    // $table->string('transaction_reference', 100)->nullable(); 
    // $table->string('description')->nullable();
    // $table->unsignedBigInteger('related_id')->nullable();
    // $table->morphs('source');
    // $table->string('status', 50)->default('pending')->comment('pending , completed , failed');
    
    
}
