<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DrieverWallet;
use Driver;
use Permission;

class DrieverWallet extends Model
{
    use HasFactory , Notifiable , HasRoles   , SoftDeletes ;
    use BelongsToOffice;
    protected $table = 'driver_wallets';
    protected $fillable = [
        'officeId', 'title', 'amount','status'
    ];

    protected $casts = [
        'userId'  =>'integer',
        'amount'   => 'double',
        'status'   => 'integer',
    ];

    public function drivers(){
        return $this->belongsTo(Driver::class, 'driverId','id');
    }
}
