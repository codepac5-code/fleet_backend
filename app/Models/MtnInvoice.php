<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MtnInvoice extends Model
{
    protected $fillable = [
        'amount', 'TTL', 'userId','phoneNumber','operationNumber','code',"orderId","guid"
    ];
}
