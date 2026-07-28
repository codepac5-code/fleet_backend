<?php
namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Booking;
use BookingRating;
use Coupon;
use Driver;
use Office;
use PaymentMethod;
use Service;
use SubService;
use User;

class Booking extends Model
{
    use HasFactory , SoftDeletes;
    use BelongsToOffice;
    protected $table = 'bookings';
    protected $fillable = [
        // 'date',
        // 'quantity',
        'startAt',
        'endAt',
        'amount',
        'discount',
        'totalAmount',
        'description',
        'couponId',
        'status',
        'startAddress',
        'startLatitude',
        'startLongitude',
        'endAddress',
        'endLatitude',
        'endLongitude',
        'distance',
        'paymentId',
        'durationDiff',
        'officeId',
        'driverId',
        'userId',
        'subServiceId',
        'multiDestnationArray',
        'time',
        'officeCommissionValue',
        'driverCommissionValue',
        'fleetCommissionValue',
        'paymentStatus',
        'PaymentDatetime',
        'paymentType',
        'isPercentage',
        'reason',
        'driverCommissionPercentage',
        'officeCommissionPercentage',
        'fleetCommissionPercentage',
        'is_scheduled', 'scheduled_time', 'isReminderSent', 'reminderSentAt',
        'stripe_payment_intent_id',
    ];





    protected $casts = [
        'scheduled_time' => 'datetime',
        'assignedAt' => 'datetime',
        'cancelledAt' => 'datetime',
        'userId'   => 'integer',
        'subServiceId'    => 'integer',
        'officeId'   => 'integer',
        'quantity'      => 'integer',
        'amount'        => 'double',
        'discount'      => 'double',
        'totalAmount'  => 'double',
        'couponId'     => 'integer',
        'paymentId'    => 'integer',
        'distance' => 'float',
    ];
    public function user(){
        // return User::where('id'=>$this->userId)
        return $this->belongsTo(User::class,'userId', 'id');
    }
    public function getDistanceAttribute($value){
    return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    public function driver(){
        return $this->belongsTo(Driver::class,'driverId', 'id');
    }






    public function scopeForCurrentUser($withTrashed = false)
    {
        $query = $this->query();

        if (Auth::guard('admin')->check()) {
            return $query->withTrashed();
        }

        else if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id)->withTrashed();
        }

        else if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->officeId) {
                return $query->where('officeId', $employee->officeId)->withTrashed();
            } else {
                return $query->withTrashed();
            }
        }

        return $query;
    }



    public function subService(){
        return $this->belongsTo(SubService::class,'subServiceId', 'id');
    }

    public function office(){
        return $this->belongsTo(Office::class,'officeId', 'id');//->withTrashed();
    }

    public function service(){
        return $this->belongsTo(Service::class,'serviceId', 'id')->withTrashed();
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class,'couponId', 'id');
    }

    public function payment(){
        return $this->belongsTo(PaymentMethod::class,'paymentId', 'id');
    }

    // public function bookingRating(){
    //     return $this->hasMany(BookingRating::class, 'service_id','service_id')->with(['customer']);
    // }

    // public function couponAdded(){
    //     return $this->belongsTo(BookingCouponMapping::class,'id','booking_id');
    // }

    // public function bookingAddonService(){
    //     return $this->hasMany(BookingServiceAddonMapping::class,'booking_id','id')->with('AddonserviceDetails');
    // }

    // public function handymanAdded(){
    //     return $this->hasMany(BookingHandymanMapping::class,'booking_id','id')->with(['handyman']);
    // }

    // public function bookingActivity(){
    //     return $this->hasMany(BookingActivity::class,'booking_id','id');
    // }

    // public function scopeMyBooking($query){
    //     $user = auth()->user();
    //     if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
    //         return $query;
    //     }

    //     if($user->hasRole('office')) {
    //         return $query->where('officeId', $user->id);
    //     }

    //     if($user->hasRole('user')) {
    //         return $query->where('userId', $user->id);
    //     }

    //     if($user->hasRole('driver')) {
    //         return $query->whereHas('driverId',function ($q) use($user){
    //             $q->where('driverId',$user->id);
    //         });
    //     }

    //     return $query;
    // }

    // public function categoryService(){
    //     return $this->belongsTo(Service::class,'service_id', 'id')->with('category');
    // }

    // public function addressAdded(){
    //     return $this->belongsTo(BookingAddressMapping::class,'id','booking_id');
    // }
    // public function bookingTaxMapping(){
    //     return $this->hasMany(BookingTaxMapping::class,'id','booking_id');
    // }
    // public function scopeShowServiceCount($query){
    //     $query->select(\DB::raw('DISTINCT service_id, COUNT(*) AS count_pid'))
    //           ->groupBy('service_id')
    //           ->orderBy('count_pid', 'desc');
    //     return $query->with('categoryService');
    // }

    // protected static function boot(){
    //     parent::boot();
    //     static::deleted(function ($row) {
    //         $row->couponAdded()->delete();
    //         $row->bookingActivity()->delete();
    //         $row->payment()->delete();
    //         $row->handymanAdded()->delete();
    //         $row->bookingRating()->delete();
    //         if($row->forceDeleting === true)
    //         {
    //             $row->couponAdded()->delete();
    //             $row->bookingActivity()->delete();
    //             $row->payment()->delete();
    //             $row->handymanAdded()->delete();
    //             $row->bookingRating()->delete();
    //         }
    //     });

    //     static::restoring(function($row) {
    //         $row->service()->withTrashed()->restore();
    //         $row->provider()->withTrashed()->restore();
    //         $row->customer()->withTrashed()->restore();
    //         $row->bookingActivity()->withTrashed()->restore();
    //         $row->couponAdded()->withTrashed()->restore();
    //         $row->payment()->withTrashed()->restore();
    //         $row->handymanAdded()->withTrashed()->restore();
    //         $row->bookingRating()->withTrashed()->restore();
    //     });
    // }

    // public function handymanByAddress(){
    //     return $this->belongsTo(ProviderAddressMapping::class,'booking_address_id','id')->with(['handyman']);
    // }
    // public function providerAddress(){
    //     return $this->belongsTo(ProviderAddressMapping::class,'booking_address_id','id');
    // }
    // public function liveLocation(){
    //     return $this->hasMany(LiveLocation::class, 'booking_id','id');
    // }
    // public function bookingExtraCharge(){
    //     return $this->hasMany(BookingExtraCharge::class, 'booking_id','id');
    // }
    // public function bookingPostJob(){
    //     return $this->belongsTo(PostJobRequest::class, 'post_request_id','id');
    // }
    // public function bookingPackage(){
    //     return $this->belongsTo(BookingPackageMapping::class, 'id','booking_id')->with('package');
    // }
    // public function scopeList($query)
    // {
    //     return $query->orderBy('updated_at', 'desc');
    // }

    // public function getHourlyPrice():float
    // {
    //     $totalOneHourSeconds = 3600;
    //     $totalMinutes = 0;

    //     $perMinuteCharge = $this->amount / 60;

    //     if ($this->duration_diff <= $totalOneHourSeconds) {
    //       $totalMinutes = $totalOneHourSeconds / 60;
    //     } else {
    //       $totalMinutes = $this->duration_diff / 60;
    //     }
    //     return $totalMinutes * $perMinuteCharge;
    // }
    // public function getServiceTotalPrice(): float
    // {
    //    $serviceTotalPrice = 0;

    //    if($this->service !== null && $this->service->type == 'hourly'){
    //     $serviceTotalPrice += $this->getHourlyPrice();
    //    }else{
    //     $serviceTotalPrice += ($this->amount) *  (!empty($this->quantity) ? $this->quantity : 1);

    //    }
    //    return $serviceTotalPrice;
    // }
    // public function getDiscountValue(): float
    // {
    //     $discount = $this->bookingPackage == null && $this->discount != 0 ? (($this->getServiceTotalPrice()/ 100) * $this->discount) : 0;

    //     return $discount  ;
    // }
    // public function getCouponDiscountValue(): float
    // {
    //     $couponAmount = 0.0;
    //     if ($this->couponAdded != null) {
    //       if ($this->couponAdded->discount_type == 'fixed') {
    //         $couponAmount = $this->couponAdded->discount;
    //       } else {
    //         $couponAmount = ($this->getServiceTotalPrice() * $this->couponAdded->discount) / 100;
    //       }
    //     }

    //     return $couponAmount;
    // }
    // public function getSubTotalValue():float
    // {
    //     $subTotal = 0;
    //     $subTotal = $this->getServiceTotalPrice() - $this->getDiscountValue() - $this->getCouponDiscountValue();

    //     return $subTotal;
    // }
    // public function getExtraChargeValue(): float
    // {
    //     $extraCharge = 0;
    //     if (!empty($this->bookingExtraCharge)) {
    //         foreach (json_decode($this->bookingExtraCharge,true) as $charge) {
    //             $extraCharge += $charge['price'] * $charge['qty'];
    //         }
    //     }

    //     return $extraCharge;
    // }
    // public function getTaxesValue(): float
    // {
    //     $total = $this->getSubTotalValue() + $this->getExtraChargeValue();
    //     $taxValue = 0;
    //     if (!empty($this->tax)) {
    //         foreach (json_decode($this->tax,true) as $tax) {
    //             if ($tax['type'] == 'percent') {
    //                 $taxValue += ($total * $tax['value'] / 100);
    //             } else {
    //                 $taxValue += $tax['value'];
    //             }
    //         }
    //     }

    //     return $taxValue;
    // }
    // public function getTotalValue(): float
    // {
    //    $grandTotalAmount =  $this->getSubTotalValue()  + $this->getTaxesValue() + $this->getExtraChargeValue();

    //    return $grandTotalAmount;
    // }
    // public function getServiceAddonValue(): float
    // {
    //     $addonPrice = 0;
    //     if (!empty($this->bookingAddonService)) {
    //         foreach ($this->bookingAddonService as $charge) {
    //             $addonPrice += $charge['price'];
    //         }
    //     }
    //     return $addonPrice;
    // }
}
