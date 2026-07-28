<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use AuditLog;

class AuditLog extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_type', 'actor_id', 'action', 'subject_type', 'subject_id', 'metadata', 'ip', 'created_at',
    ];

    protected $casts = [
        'actor_id' => 'integer',
        'subject_id' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}
