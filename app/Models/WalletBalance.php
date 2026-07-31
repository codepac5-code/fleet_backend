<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A balance belongs to the country whose ledger produced it.
 */
class WalletBalance extends Model
{
    use ResolvesTenantConnection;

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
