<?php

use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;

return [

    ErrorMessages::$invalidData => " :attributes الداتا المدخلة غير صحيحة",
    ErrorMessages::$notExait => ":attributes الحساب غير  موجود",
    ErrorMessages::$endOtp => "الكود غير صحيح الرجاء اعادة المحاولة",
    ErrorMessages::$AccountAlreadyExists => "حساب :attributes موجود مسبقاً",
    ErrorMessages::$SomeThingWentWrong => "حدث خطأ ما",
    SuccessMessages::$ratting_success => "تم التقييم  بنجاح",
    'something_wrong' => 'عذرًا! حدث خطأ ما. حاول مرة أخرى!',
    'insufficient_balance' => 'لا يوجد رصيد كافٍ في محفظتك.',

    


    'attributes' => [
    Attributes::User->value                  => 'المستخدم',
    Attributes::Admin->value             => 'المدير',
    Attributes::Driver->value                => 'السائق',
    Attributes::None->value                => ""
    ],

];



