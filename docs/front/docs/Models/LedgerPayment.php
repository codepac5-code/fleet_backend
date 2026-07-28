<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class LedgerPayment extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'ledger_payments';

    protected $fillable = [
        'uuid',
        'idempotency_key',
        'provider',
        'provider_ref',
        'kind',
        'owner_type',
        'owner_id',
        'booking_id',
        'amount_minor',
        'currency_code',
        'status',
        'ledger_transaction_uuid',
        'meta',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'booking_id' => 'integer',
        'amount_minor' => 'integer',
        'meta' => 'array',
    ];
}
