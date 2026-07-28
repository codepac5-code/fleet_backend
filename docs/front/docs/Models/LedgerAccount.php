<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'ledger_accounts';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'account_type',
        'currency_code',
        'balance_minor',
        'code',
    ];

    protected $casts = [
        'balance_minor' => 'integer',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'account_id');
    }
}
