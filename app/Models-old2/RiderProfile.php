<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RiderProfile;

class RiderProfile extends Model
{
    protected $connection = 'global';

    protected $table = 'rider_profiles';

    protected $fillable = ['user_id', 'email', 'locale'];

    protected $casts = ['user_id' => 'integer'];
}
