<?php

namespace App\Models;

use App\Traits\BelongsToOffice;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use UserNotification_model;
class UserNotification_model extends Authenticatable
{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , SoftDeletes ;
    use BelongsToOffice;



    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'officeId',
        'lastName',
        'phoneNumber',
        'gender',
        'is_registered',
        'photo',
        'walletBalance',
        'password',
        'isActive'
    ];


    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'notification-user.'.$this->id;
    }

}
