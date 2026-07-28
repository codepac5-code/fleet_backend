<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use WalletTransaction;
use WalletTransactionGroup;

class WalletTransactionGroup extends Model
{
    protected $table = 'wallet_transaction_groups';

    protected $fillable = [
        'transaction_reference',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'description_en',
        'paymentName',
        'paymentName_en',
        'source_type',
        'source_id',
        'status',
        'transaction_type',
    ];


    public function from()
    {
        return $this->morphTo();
    }


    public function to()
    {
        return $this->morphTo();
    }


    public function source()
    {
        return $this->morphTo();
    }


    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'transaction_reference', 'transaction_reference');
    }
}
