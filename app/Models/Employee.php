<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Model
{

    use HasFactory, Notifiable , HasApiTokens , HasRoles , InteractsWithMedia  , SoftDeletes ;

    
    protected $table = 'employees';

    protected $fillable = [
        'remember_token',
        'firstName',
        'lastName',
        'email',
        'password',
        'photo',
        'gender',
        'officeId',
        'address',
        'country',
        'city',
        'region',
        'isActive',
        'isOnline',
        'employeeJobName_en',
        'employeeJobName_ar',
        'job_description_en',
        'job_description_ar',
    ];
}
