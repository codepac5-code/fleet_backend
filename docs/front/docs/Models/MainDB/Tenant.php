<?php

namespace App\Models\MainDB;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'mysql'; 
    protected $fillable = ['name', 'database', 'domain'];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class);
    }
}
