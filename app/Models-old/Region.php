<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use City;
use Region;

class Region extends Model
{
    use HasFactory;//region
    protected $table = "cities";
    protected $primaryKey = "id";

    protected $casts = [
        'cityId'  => 'integer',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'cityId','id');
    }
}
