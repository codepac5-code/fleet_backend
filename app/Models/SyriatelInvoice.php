<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyriatelInvoice extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'orderId', 'phoneNumber', 'userId','amount','code','token',"merchantMSISDN"
    ];


    /**
     * Get the user that owns the SyriatelInvoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the user that owns the SyriatelInvoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(booking::class);
    }
}
