<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Follows its issue onto the country shard.
 */
class IssueLog extends Model
{
    use ResolvesTenantConnection;

    protected $fillable = ['issue_id', 'employee_id','employee_type', 'action', 'note'];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function employee()
    {
        return $this->morphTo('employee');
    }
}

