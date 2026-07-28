<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletBalance extends Model
{
    protected $table = 'wallet_balances';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'currency_code',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
