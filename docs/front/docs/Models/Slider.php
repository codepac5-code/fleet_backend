<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends Model
{
    use HasFactory , SoftDeletes;
    
    protected $table = 'sliders';


    protected $fillable = [
        'image',
        'title',
        'description',
        'isActive',
        'description_en',
        'title_en',
    ];


    public static function SelectWithTranslate(){

        switch(app()->getLocale())
        {
            case 'ar': 
                return Slider::select([
                    'image',
                    'title',
                    'description',
                    'isActive',
                ]);
          
            case 'en': 
                return Slider::select([
                    'title_en as title',
                    'description_en as description',
                    'image',
                    'isActive',
                ]);

            default :
            return Slider::select([
                'image',
                'title',
                'description',
                'isActive',
              ]);
        }
    }

}
