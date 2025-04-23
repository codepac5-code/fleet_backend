<?php

use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Options\LanguageOptions;


function checkLangAndSendMessage($message){

    if (in_array(request()->header('localization'),LanguageOptions::$language)) {

        $str = explode(':',$message);

        if (count($str)==1) {
            $str[1]=$str[0];
            $str[0] = Attributes::None->value;
        }
        return __($str[1],['attributes' => __('messages.attributes.' . $str[0])] ,request()->header('localization'));
    }

    $str = explode(':',$message);

    if (count($str)==1) {
        $str[1]=$str[0];
        $str[0] = Attributes::None->value;
    }

    return __($str[1], ['attributes' => __('messages.attributes.' . $str[0])] ,"ar");


}
