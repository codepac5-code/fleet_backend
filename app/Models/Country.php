<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;
    protected $table = "countries";
    protected $primaryKey = "id";
    protected $casts = [
        'dial_code' => 'integer',
    ];
    
    public function states()
    {
        return $this->hasMany(State::class, 'country_id','id');
    }
}
