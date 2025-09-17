<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class officeUserStats extends Model
{
    protected $table = 'office_user_stats';

    protected $fillable = ['officeId', 'userId', 'totalBookings', 'totalAmount', 'totalDistance', 'lastBookingAt', 'averageRating', 'lastPaymentStatus'];

    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
