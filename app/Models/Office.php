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

class Office extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens , HasRoles , InteractsWithMedia  , SoftDeletes , ResolvesTenantConnection ;

    protected $table = 'offices';
    protected $guard_name = 'office';

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($permission instanceof \BackedEnum) {
            $permission = $permission->value;
        }

        if (is_object($permission)) {
            $permission = $permission->name ?? null;
        }

        if (is_string($permission)) {
            return $this->getAllPermissions()->contains('name', $permission);
        }

        if (is_int($permission)) {
            $resolved = Permission::on($this->getConnectionName())->find($permission);

            return $resolved !== null && $this->getAllPermissions()->contains('name', $resolved->name);
        }

        return false;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * Seeded rows carry the literal string "NULL" in `displayName`, which is not
     * null — so every `?? $officeName` fallback in the panel printed the word
     * NULL instead of the office's name. Normalise it at the model so every
     * screen, export and API sees a real absence.
     */
    public function getDisplayNameAttribute($value)
    {
        $value = is_string($value) ? trim($value) : $value;

        return ($value === null || $value === '' || strcasecmp((string) $value, 'null') === 0) ? null : $value;
    }

    protected $fillable =
    [
        'profileImage', 'logo',  'email','officeName','limitOrders',
        'password', 'address', 'contactNumber',
        'email_verified_at', 'remember_token','country','region','city','status',
        'displayName','officeTypeId','timeZone','last_notification_seen','email_verified_at',
        // rider-app card fields (see 2026_07_15_000001_add_rider_api_missing_columns)
        'initials','palette','is_verified','is_monitored',
        'on_time_percentage','avg_response_minutes','ratings_count',
        'lat','lng','working_hours',
        // The three-way split: what the platform takes from this office, and
        // what this office takes from its drivers. Null = follow the default.
        'fleet_commission_rate','driver_commission_rate',
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


    /**
     * The main services this office is set up under — plural, because one
     * company routinely runs the city meter service AND sells airport
     * corridors. Everything it may price hangs off these: meter rates for a
     * meter service's sub-services, a price per corridor for a travel one.
     */
    public function serviceIds(): array
    {
        return $this->services()->pluck('services.id')->map(fn ($id) => (int) $id)->all();
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
    return $this->belongsToMany(Service::class, 'office_services', 'officeId', 'serviceId')->withTimestamps();
}

}
