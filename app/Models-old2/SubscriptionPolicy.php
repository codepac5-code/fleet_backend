<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SubscriptionPolicy;

class SubscriptionPolicy extends Model
{
    protected $table = 'subscription_policies';


    protected $fillable = [
        'label',
        'text',
        'lang'
    ];


}
