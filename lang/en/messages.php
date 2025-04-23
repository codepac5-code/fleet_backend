<?php

// lang/en/messages.php

use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;

return [
    'welcome' => 'Welcome to our application!',
    ErrorMessages::$invalidData => ":attributes invalid data",
    ErrorMessages::$notExait => "account :attributes not exists",
    ErrorMessages::$endOtp => "invalid otp try agin",
    ErrorMessages::$SomeThingWentWrong => "somthing error",
    SuccessMessages::$ratting_success => "ratting success",
    'something_wrong' => 'Oops! Something went wrong. Let’s try again!',
    'insufficient_balance' => 'You do not have enough balance in your wallet.',

    ErrorMessages::$AccountAlreadyExists => "acount :attributes already exists",

    'attributes' => [
        Attributes::User->value                  => 'user',
        Attributes::Admin->value             => 'admin',
        Attributes::Driver->value                => 'driver',
        Attributes::None->value                => ""
        ],

];
