<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Currency;

class Currency extends Model
{
    protected $connection = 'global';

    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimals',
        'exchange_rate',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'decimals'      => 'integer',
        'exchange_rate' => 'decimal:6',
        'is_default'    => 'boolean',
        'is_active'     => 'boolean',
    ];
}
