<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class UserWallet extends Model
{
    use HasFactory , Notifiable , HasRoles   , SoftDeletes ;

    protected $table = 'user_wallets';
    protected $fillable = [
        'officeId', 'title', 'amount','status'
    ];

    protected $casts = [
        'userId'  =>'integer',
        'amount'   => 'double',
        'status'   => 'integer',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'userId','id');
    }
}
