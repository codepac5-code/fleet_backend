<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouponService extends Model{
    use HasFactory, SoftDeletes, ResolvesTenantConnection;
    protected $table = 'coupon_services';
    protected $dates = ['deleted_at'];

    protected $fillable = [ 'couponId', 'serviceId' ];

    protected $casts = [
        'couponId'     => 'integer',
        'serviceId'    => 'integer',
    ];
    
    public function coupon(){
        return $this->belongsTo(Coupon::class,'couponId','id');
    }

    public function service(){
        return $this->belongsTo(Service::class,'serviceId','id');
    }
}
