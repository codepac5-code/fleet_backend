<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name_en', 'name_ar'];

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}

