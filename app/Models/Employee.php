<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;

class Employee extends Authenticatable implements HasMedia
{

    use HasFactory, Notifiable , HasApiTokens , HasRoles , InteractsWithMedia  , SoftDeletes ,InteractsWithMedia ;

    protected $guard_name = 'employee';

    protected $table = 'employees';
    
    // protected $guard_name = 'employee';


    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'phoneNumber',
        'employeeJobName_en',
        'employeeJobName_ar',
        'job_description_en',
        'job_description_ar',
        'officeId',
        'address',
        'country',
        'city',
        'region',
        'isActive',
        'isOnline',
        'gender',
        'password',
        'photo',
        'role', 
    ];


    public function departments(){
    return $this->belongsToMany(Department::class);
    }


    public function assignedIssues()
    {
        return $this->morphMany(Issue::class, 'assigned_to');
    }

    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles', 'model_id', 'role_id');
    }


    public function scopeForCurrentUser()
    {
        $query = $this->query();

        if (Auth::guard('admin')->check()) {
            return $query;
        }

        if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id);
        }

        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->office_id) {
                return $query->where('officeId', $employee->officeId);
            } else {
                return $query;
            }
        }

        return $query;
    }
}
