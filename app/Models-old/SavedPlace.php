<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SavedPlace;

class SavedPlace extends Model
{
    protected $connection = 'global';

    protected $table = 'saved_places';

    protected $fillable = ['user_id', 'label', 'title', 'lat', 'lng'];

    protected $casts = [
        'user_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
    ];
}
