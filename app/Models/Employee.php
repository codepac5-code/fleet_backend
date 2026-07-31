<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
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
    use BelongsToOffice;
    protected $guard_name = 'employee';

    protected $table = 'employees';

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($permission instanceof \BackedEnum) {
            $permission = $permission->value;
        }

        if (is_object($permission)) {
            $permission = $permission->name ?? null;
        }

        if (is_string($permission)) {
            return $this->getAllPermissions()->contains('name', $permission);
        }

        if (is_int($permission)) {
            $resolved = Permission::on($this->getConnectionName())->find($permission);

            return $resolved !== null && $this->getAllPermissions()->contains('name', $resolved->name);
        }

        return false;
    }

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
        return $this->morphToMany(PlatformRole::class, 'model', 'model_has_roles', 'model_id', 'role_id');
    }

    /**
     * Employees are PER SHARD while the permission catalog is platform-wide, so
     * both the lookup and the pivot are pinned to the platform connection — see
     * [PlatformPermission] for what went wrong when they were split.
     */
    public function permissions()
    {
        return $this->morphToMany(PlatformPermission::class, 'model', 'model_has_permissions', 'model_id', 'permission_id');
    }

    /**
     * Employee ids REPEAT across country databases, so a shared pivot table
     * would let office #26 in one country inherit the permissions of employee
     * #26 in another. Qualifying the morph class with the active shard keeps
     * each country's grants separate inside the one table.
     */
    public function getMorphClass(): string
    {
        $shard = \App\Http\Core\GeoServices\ShardManager::shardKey();

        return $shard !== '' ? static::class . '@' . $shard : static::class;
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
