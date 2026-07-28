<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Booking;
use Driver;
use Office;
use Rating;
use User;

class Rating extends Model
{
    use HasFactory, SoftDeletes;
    use BelongsToOffice;


    protected $fillable = [
        'rater_id', 'rater_type',
        'rated_person_id', 'rated_person_type',
        'description', 'rating',
        'orderId', 'officeId'
    ];


    public function scopeForCurrentUser()
    {
        $query = $this->query();

        if (Auth::guard('admin')->check()) {
            return $query;
        }

        if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id);
        }

        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->office_id) {
                return $query->where('officeId', $employee->officeId);
            } else {
                return $query;
            }
        }

        return $query;
    }

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
