<?php
namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens , HasRoles , InteractsWithMedia  , SoftDeletes ;

    protected $table = 'offices';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = 
    [
        'profileImage', 'logo',  'email','officeName','limitOrders',
        'password', 'address', 'contactNumber',
        'email_verified_at', 'remember_token','country','region','city','status',
        'displayName','officeTypeId','timeZone','last_notification_seen','email_verified_at',
        // 'commissionType', 'commissionValue',
        'ratingExcellent',
        'ratingGood',
        'ratingAverage',
        'ratingBelowAverage',
        'ratingPoor',
        'rating',
        'commission_with_office_car',
        'commission_with_driver_car',
        'driver_commission_precentage',
        'driver_car_commission_precentage',
        'walletBalance'
    ];

    public function getCommissionFormattedAttribute()
    {
        if ($this->commissionType === 'percentage') {
            return $this->commissionValue . '%'; 
        }
        return number_format($this->commissionValue, 2) . ' ' . __('messages.currency'); 
    }

    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get all of the wallets for the Office
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallets()
    {
        return $this->hasMany(OfficeWallet::class, 'officeId', 'id');
    }

    public function booking(){
        return $this->hasMany(Booking::class, 'officeId','id');
    }


    public function officeBooking(){
        return $this->hasMany(Booking::class, 'officeId','id');
    }


    public function officeDocument(){
        return $this->hasMany(OfficeDocument::class, 'officeId','id');
    }

    
    public function drivers(){
        return $this->hasMany(Driver::class, 'officeId','id');
    }

    public function ratings(){
        return $this->hasMany(Rating::class, 'officeId','id');
    }

    
}
