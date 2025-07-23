<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserReport extends Model
{
    use HasFactory;

    protected $table = 'user_reports';

    protected $fillable = [
        'subject',
        'description',
        'photo',
        'userId',
        'isClosed',
        'closedAt',
    ];


    /**
     * Get the user that owns the UserReport
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }


    public function replies()
    {
        return $this->hasMany(Reply::class, 'issueId');
    }
}
