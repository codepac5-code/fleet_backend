<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin  extends Authenticatable implements HasMedia
{
    use InteractsWithMedia  , SoftDeletes , HasRoles ;


    protected $table = 'admins';

    protected $fillable = [
        'firstName',
        'lastName',
        'gender',
        'email',
        'password',
        'photo',
        'deleted_at'

    ];


    public function assignedIssues(){
    return $this->morphMany(Issue::class, 'assigned_to');
    }


}
