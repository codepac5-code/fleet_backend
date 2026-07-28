<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'name_en',
        'image',
        'status',
        'payment_wallet',
        'payment_trip',
        'type',
    ];

    protected $hidden = [
        'status'
    ];

}
