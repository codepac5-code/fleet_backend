<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rater_id', 'rater_type',
        'rated_person_id', 'rated_person_type',
        'description', 'rating',
        'orderId', 'officeId'
    ];

    /**
     * Get the booking that owns the Rating
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'orderId');
    }

    public function rater()
    {
        return $this->morphTo();
    }

    public function ratedPerson()
    {
        return $this->morphTo();
    }

    /**
     * Get the driver that owns the Rating
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }


    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId');
    }
}
