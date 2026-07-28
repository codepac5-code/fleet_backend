<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Driver;
use DriverRepliesIssue;
use DriversIssue;
use UserReport;

class DriversIssue extends Model
{
    use HasFactory;

    protected $table = 'drivers_issues';

    protected $fillable = [
        'subject',
        'description',
        'photo',
        'driverId',
        'isClosed',
        'closedAt',
    ];


    /**
     * Get the user that owns the UserReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function drivers()
    {
        return $this->belongsTo(Driver::class, 'driverId', 'id');
    }


    public function replies()
    {
        return $this->hasMany(DriverRepliesIssue::class, 'issueId');
    }
}
