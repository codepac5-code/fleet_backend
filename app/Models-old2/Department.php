<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Department;
use Employee;
use Issue;

class Department extends Model
{
    protected $fillable = ['name_en', 'name_ar'];

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function employees()
    {
    return $this->belongsToMany(Employee::class);
    }

}

