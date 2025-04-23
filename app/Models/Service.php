<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Service extends  Model implements HasMedia
{
    use InteractsWithMedia  , SoftDeletes , HasFactory;


    protected $table = 'services';

    protected $fillable = [
        'name',
        'status',
        'description',
        'image',
        'title',
        'title_en',
        'description_en',
    ];


    public static function SelectWithTranslate(){

        switch(app()->getLocale())
        {
            case 'ar': 
                return Service::select([
                  'status',
                  'description',
                  'image',
                  'title',
                ]);
          
            case 'en': 
                return Service::select([
                  'status',
                  'title_en as title',
                  'description_en as description',
                  'image',
                ]);

            default :
            return Service::select([
                'status',
                'description',
                'image',
                'title',
              ]);
        }
     

    }

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
