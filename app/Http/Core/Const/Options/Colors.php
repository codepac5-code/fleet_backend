<?php 
namespace App\Http\Core\Const\Options;

abstract class Colors  {
    public static  $colors_ar = ['ابيض','اصفر','ازرق','احمر','اسود','اخضر'];
    public static  $colors_en = ['blue','red','yellow','green','black','white'];



    public static function get_colors(){
        if(app()->getLocale() == 'ar')
        {
            return self::$colors_ar;
        }

        return self::$colors_en;
    }
}

