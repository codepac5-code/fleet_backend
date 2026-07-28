<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Issue;
use IssueLog;

class IssueLog extends Model
{
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

