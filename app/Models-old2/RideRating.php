<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use RideRating;

class RideRating extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'ride_ratings';

    protected $fillable = [
        'booking_id', 'rater_type', 'rater_id', 'ratee_type', 'ratee_id',
        'stars', 'tags', 'comment', 'book_again', 'favorite', 'created_at',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'rater_id' => 'integer',
        'ratee_id' => 'integer',
        'stars' => 'integer',
        'tags' => 'array',
        'book_again' => 'boolean',
        'favorite' => 'boolean',
        'created_at' => 'datetime',
    ];
}
