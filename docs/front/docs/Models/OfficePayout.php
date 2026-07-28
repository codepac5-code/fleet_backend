<?php
namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficePayout extends Model
{
    use HasFactory;
    use BelongsToOffice;

    protected $table = 'office_payouts';
    protected $fillable = [
        'officeId', 'payment_method', 'description','amount','status','paid_date','bank_id',
    ];
    protected $casts = [
        'officeId'     => 'integer',
        'amount'    => 'double',
    ];
    public function providers(){
        return $this->belongsTo(Office::class, 'officeId','id');
    }
    public function scopeMyPayout($query)
    {
        // if(auth()->user()->hasRole('admin')) {
        //     return $query;
        // }

        // if(auth()->user()->hasRole('office')) {
        //     return $query->where('officeId', Auth::id());
        // }

        return $query;
    }}
