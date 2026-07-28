<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LedgerAccount;
use LedgerEntry;
use LedgerTransaction;

class LedgerEntry extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'ledger_entries';

    protected $fillable = [
        'transaction_id',
        'account_id',
        'direction',
        'amount_minor',
        'currency_code',
        'balance_after_minor',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'balance_after_minor' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(LedgerTransaction::class, 'transaction_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id');
    }
}
