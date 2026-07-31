<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfrastructureNode extends Model
{
    protected $connection = 'global';

    protected $fillable = [
        'type',
        'name',
        'parent_id',
        'country_code',
        'billing_mode',
        'currency_code',
        'currency_symbol',
        'city',
        'lat',
        'lng',
        'radius_km',
        'db_host',
        'db_name',
        'db_user',
        'db_pass',
        'db_port',
        'redis_host',
        'redis_db',
        'redis_prefix',
        'is_active',
        'provisioned_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
    ];

    public function isProvisioned(): bool
    {
        return $this->provisioned_at !== null;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
