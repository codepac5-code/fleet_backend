<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToOffice;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Booking;
use Issue;
use Office;
use OfficeDocument;
use Permission;
use Rating;
use Reply;

class User extends Authenticatable
{


    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens , HasRoles , SoftDeletes ;
    // use BelongsToOffice;


    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'officeId',
        'lastName',
        'phoneNumber',
        'gender',
        'is_registered',
        'photo',
        'walletBalance',
        'password',
        'isActive',
        'referralCode',
        'dialCode',
        'stripe_customer_id',
    ];

    public function assignedIssues()
    {
        return $this->morphMany(Issue::class, 'assigned_to');
    }



    public function scopeForCurrentUser()
    {
        $query = $this->query()->withTrashed();

        if (Auth::guard('admin')->check()) {
            return $query;
        }

        if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id);
        }

        if ( Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->office_id) {
                return $query->where('officeId', $employee->officeId);
            } else {
                return $query;
            }
        }
        return $query;
    }

    public function replies()
    {
    return $this->morphMany(Reply::class, 'sender');
    }

    public function officesStats()
    {
        return $this->belongsToMany(Office::class, 'office_user_stats', 'userId', 'officeId')
                    ->withPivot('totalBookings', 'totalAmount', 'totalDistance', 'lastBookingAt', 'averageRating', 'lastPaymentStatus')
                    ->withTimestamps();
    }

    public function issues()
    {
        return $this->morphMany(Issue::class, 'owner');
    }
    public function bookings() {
        return $this->hasMany(Booking::class, 'userId');
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_registered',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The channels the user receives notification broadcasts on.
     */

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'private-notification-user.'.$this->id;
    }



    public function ratingsReceived()
    {
    return $this->morphMany(Rating::class, 'rated_person');
    }

    public function ratingsGiven()
    {
    return $this->morphMany(Rating::class, 'rater');
    }


    public function booking(){
        return $this->hasMany(Booking::class, 'userId','id');
    }

    public function officeDocument(){
        return $this->hasMany(OfficeDocument::class, 'officeId','id');
    }

}
