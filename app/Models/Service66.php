<?php
namespace App\Models;


use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model implements HasMedia
{
    use InteractsWithMedia  , SoftDeletes ; 


    protected $table = 'services';

    protected $fillable = [
        'name',
        'status',
        'description',
        'image'

    ];

    /**
     * Get all of the subServices for the Service
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subServices()
    {
        return $this->hasMany(SubService::class , "serviceId");
    }
}
