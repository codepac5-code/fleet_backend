<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class PlanOverageCharge extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'plan_overage_charges';

    protected $fillable = [
        'office_id',
        'period',
        'type',
        'reference_type',
        'reference_id',
        'amount_minor',
        'currency_code',
        'status',
        'invoice_ref',
        'collection_method',
        'external_ref',
        'invoiced_at',
        'collected_at',
        'created_at',
    ];

    protected $casts = [
        'office_id' => 'integer',
        'reference_id' => 'integer',
        'amount_minor' => 'integer',
        'invoiced_at' => 'datetime',
        'collected_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
