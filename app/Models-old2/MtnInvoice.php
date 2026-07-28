<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MtnInvoice;

class MtnInvoice extends Model
{
    protected $fillable = [
        'amount', 'TTL', 'userId','phoneNumber','operationNumber','code',"orderId","guid"
    ];
}
