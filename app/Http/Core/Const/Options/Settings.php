<?php
namespace App\Http\Core\Const\Options;

use App\Http\Core\Const\Options\LanguageOptions;



class Settings {

public static function getLanguage() : string {

    if (in_array(request()->header('localization') , LanguageOptions::$language )){
     return request()->header('localization');
    }
    return config('app.locale');
    }

}

