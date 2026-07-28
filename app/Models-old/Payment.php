<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Booking;
use Payment;
use PaymentHistory;
use User;

class Payment extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'userId',
        'bookingId',
        'datetime',
        'discount',
        'totalAmount',
        'paymentType',
        'paymentStatus',
        'otherTransactionDetail'
    ];

    protected $casts = [
        'bookingId'     => 'integer',
        'userId'        => 'integer',
        'discount'      => 'double',
        'totalAmount'   => 'double',
    ];

    public function customer(){
        return $this->belongsTo(User::class,'customer_id', 'id')->withTrashed();
    }
    public function booking(){
        return $this->belongsTo(Booking::class,'booking_id', 'id')->withTrashed();
    }


    public function scopeMyPayment($query)
    {
        $user = auth()->user();
        if($user->hasAnyRole(['admin', 'demo_admin'])){
            return $query;
        }

        if($user->hasRole('provider')) {
            return $query->whereHas('booking', function($q) use($user) {
                $q->where('provider_id', '=', $user->id);
            });
        }

        if($user->hasRole('user')) {
            return $query->where('customer_id', $user->id);
        }

        if($user->hasRole('handyman')) {
            return $query->whereHas('booking',function ($q) use($user) {
                $q->whereHas('handymanAdded',function($handyman) use($user){
                    $handyman->where('handyman_id',$user->id);
                });
            });
        }

        return $query;
    }
    public function paymentHistory(){
        return $this->hasMany(PaymentHistory::class, 'payment_id','id');
    }
}
