<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens , HasRoles , SoftDeletes ;

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
        'isActive',
        'referralCode',
        'dialCode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_registered',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'private-notification-user.'.$this->id;
    }

    

    public function ratingsReceived()
    {
    return $this->morphMany(Rating::class, 'rated_person');
    }

    public function ratingsGiven()
    {
    return $this->morphMany(Rating::class, 'rater');
    }

    
    public function booking(){
        return $this->hasMany(Booking::class, 'userId','id');
    }

    public function officeDocument(){
        return $this->hasMany(OfficeDocument::class, 'officeId','id');
    }    

}
