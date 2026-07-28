<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Office;
use OfficeSubServicePrice;
use SubService;

class OfficeSubServicePrice extends Model
{
    protected $fillable = [
        'office_id',
        'sub_service_id',
        'openPrice',
        'kmPrice',
        'minutePrice',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function subService()
    {
        return $this->belongsTo(SubService::class);
    }


}
