<?php
namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Driver extends Authenticatable
{
    use HasFactory, Notifiable , HasApiTokens , HasRoles ,  InteractsWithMedia  , SoftDeletes  ;

    protected $table = 'drivers';
    // protected $primaryKey = 'id';

    protected $fillable = [
        'remember_token',
        'firstName',
        'lastName',
        'email',
        'password',
        'userName',
        'photo',
        'gender',
        'officeId',
        'address',
        'country',
        'city',
        'isConected',
        'region',
        'isActive',
        'status',
        'rating',
        'rideCount',
        'kmCount',
        'phoneNumber',
        'is_registered',
        'walletBalance',
        'free_driver',
        'ratingExcellent',
        'ratingGood',
        'ratingAverage',
        'ratingBelowAverage',
        'ratingPoor',
        'car_owner',
        'fleetDues',
        'officeDues',
        'vehicleId',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'is_registered',
    ];

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'private-notification-driver.'.$this->id;
    }

    public function ratingsReceived()
    {
    return $this->morphMany(Rating::class, 'rated_person');
    }   

    public function ratingsGiven()
    {
        return $this->morphMany(Rating::class, 'rater');
    }


    public function has_sub_service($subServiceId) : bool
    {  
       $subServiceId = intval($subServiceId);
       $subServiceIds = $this->hasMany(Vehicle_SubService::class, 'vehicleId', 'vehicleId')
      ->where(['subServiceId'=>$subServiceId])
      ->pluck('subServiceId')
      ->toArray();

      Log::info('driver has this sub services: ',$subServiceIds);
      return !empty($subServiceIds);
    }


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
            'isConected' => 'boolean'
        ];
    }




    /**
     * Get the office that owns the Driver
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId');
    }

    /**
     * Get the car associated with the Driver
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicleId');
    }
        /**
     * Get the office that owns the Driver
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'addressId');
    }


    // public function booking()
    // {
    //     return $this->belongsTo(Booking::class, 'id');
    // }
}
