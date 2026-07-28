<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use CorporateInvoice;
use User;

/**
 * B2B monthly ride invoice. Backs GET /corporate/invoices.
 * Money is stored in minor units (amount_minor) with currency_code.
 * @see migration 2026_07_15_000001_add_rider_api_missing_columns
 */
class CorporateInvoice extends Model
{
    protected $connection = 'global';

    protected $table = 'corporate_invoices';

    protected $fillable = [
        'user_id', 'month', 'trips', 'amount_minor', 'currency_code', 'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'trips' => 'integer',
        'amount_minor' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
