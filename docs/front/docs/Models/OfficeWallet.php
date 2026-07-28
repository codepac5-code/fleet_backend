<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeWallet extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    use HasFactory, Notifiable , HasRoles   , SoftDeletes ;
    use BelongsToOffice;


    protected $table = 'office_wallets';
    protected $fillable = [
        'officeId', 'title', 'amount','status'
    ];

    protected $casts = [
        'offceId'  =>'integer',
        'amount'   => 'double',
        'status'   => 'integer',
    ];

    public function offices(){
        return $this->belongsTo(Office::class, 'officeId','id');
    }

}
