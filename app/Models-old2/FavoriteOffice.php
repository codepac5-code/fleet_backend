<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use FavoriteOffice;

class FavoriteOffice extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'favorite_offices';

    protected $fillable = ['user_id', 'office_id'];

    protected $casts = [
        'user_id' => 'integer',
        'office_id' => 'integer',
    ];
}
