<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LedgerEntry;
use LedgerTransaction;

class LedgerTransaction extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'ledger_transactions';

    protected $fillable = [
        'uuid',
        'idempotency_key',
        'reference_type',
        'reference_id',
        'kind',
        'currency_code',
        'status',
        'description',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'transaction_id');
    }
}
