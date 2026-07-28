<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commissions extends Model
{
    protected $table = 'commissions';
    use HasFactory;
    protected $fillable = ['type','office_percentage'
    , 'driver_percentage', 'fleet_percentage'];
}