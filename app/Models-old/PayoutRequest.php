<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use PayoutRequest;

class PayoutRequest extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'payout_requests';

    protected $fillable = [
        'owner_type', 'owner_id', 'source_account', 'amount_minor',
        'currency_code', 'status', 'idempotency_key', 'ledger_transaction_uuid', 'note', 'processed_at',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'amount_minor' => 'integer',
        'processed_at' => 'datetime',
    ];
}
