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
use Illuminate\Support\Facades\Auth;
use App\Traits\ResolvesTenantConnection;
use Booking;
use Driver;
use Office;
use OfficeDocument;
use OfficeSubServicePrice;
use OfficeWallet;
use Permission;
use Rating;
use Service;
use SubService;
use Vehicle;

class Office extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens , HasRoles , InteractsWithMedia  , SoftDeletes , ResolvesTenantConnection ;

    protected $table = 'offices';
    protected $guard_name = 'office';
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
        'walletBalance',
        'fleetDues',
        'driverDues',
        'isFleetCommissionCustom',
        'FleetCommissionCustomValue',
        'commissionCustomValue',


        //-----------
        // 'total_income',
        // 'withdrawn_amount',
        // 'available_amount',
        // 'drivers_debt',
        // 'fleet_debt',
        // 'drivers_count',

    ];

    public function subServicePrices()
    {
        return $this->hasMany(OfficeSubServicePrice::class);
    }

    public function subServices()
    {
        return $this->belongsToMany(SubService::class, 'office_sub_service_prices')
            ->withPivot(['openPrice', 'kmPrice', 'minutePrice'])
            ->withTimestamps();
    }


    public function customerStats()
    {
        return $this->belongsToMany(User::class, 'office_user_stats', 'officeId', 'userId')
                    ->withPivot('totalBookings', 'totalAmount', 'totalDistance', 'lastBookingAt', 'averageRating', 'lastPaymentStatus')
                    ->withTimestamps();
    }

    public function scopeForCurrentUser()
    {
        $query = $this->query();

        if (Auth::guard('admin')->check()) {
            return $query;
        }

        if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('id', $office->id);
        }

        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->office_id) {
                return $query->where('id', $employee->officeId);
            } else {
                return $query;
            }
        }

        return $query;
    }

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

    public function vehicles(){
        return $this->hasMany(Vehicle::class, 'officeId','id');
    }
    public function ratings(){
        return $this->hasMany(Rating::class, 'officeId','id');
    }

public function services()
{
    return $this->belongsToMany(Service::class, 'office_services', 'officeId', 'serviceId');
}

}
