<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponUser extends Model
{
    use HasFactory, SoftDeletes, ResolvesTenantConnection;
    protected $table = 'coupon_users';
    protected $dates = ['deleted_at'];


    protected $fillable = [ 'couponId', 'userId', 'count' ];

    protected $casts = [
        'couponId'     => 'integer',
        'serviceId'    => 'integer',
    ];
    public function coupon(){
        return $this->belongsTo(Coupon::class,'couponId','id');
    }

    public function user(){
        return $this->belongsTo(User::class,'userId','id');
    }
}
