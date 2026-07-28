<?php
namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Booking;
use Driver;
use Rating;
use RatingUser;
use User;

class RatingUser extends Model
{
    use HasFactory , SoftDeletes;
    use BelongsToOffice;

    protected $table = 'rating_users';
    protected $fillable = [
        'userId',
        'orderId',
        'description',
        'rating',
        'driverId',
        'officeId',
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


    /**
     * Get the user that owns the Rating
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

}
