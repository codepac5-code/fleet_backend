<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Support tickets belong to the country they were raised in.
 */
class Issue extends Model
{
    use ResolvesTenantConnection;

    use HasFactory;

    protected $table = 'issues';

    protected $fillable = [
        'owner_id',
        'owner_type',
        'subject',
        'description',
        'mode',
        'photo',
        'isClosed',
        'closedAt',
        'status',
        'priority',
        'assigned_to',
        'department_id',
    ];

    public function assignedIssues()
{
    return $this->morphMany(Issue::class, 'assigned_to');
}

    

    public function department(){
        return $this->belongsTo(Department::class);
    }


    public function logs(){
        return $this->hasMany(IssueLog::class);
    }


    public function assignedTo()
    {
        return $this->morphTo('assigned_to');
    }



    /**
     * Get the user that owns the UserReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->morphTo();
    }


    public function replies()
    {
        return $this->hasMany(Reply::class, 'issueId');
    }
}
