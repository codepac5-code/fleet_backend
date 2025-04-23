<?php
namespace App\Http\Core\Const\Messages;


class SuccessMessages{

    static public $AccountCreated = 'account_created';
    static public $couponData = 'coupon_data';
    static public $logout = 'logout_success';
    static public $order_Send = 'order_send';
    static public $ratting_success = 'ratting_success';
    static public $CommpletedOrder= 'order completed successfully';

    static function getKey(string $key , Attributes $attribute = Attributes::None){
        return $attribute->value.":messages." . $key;
    }


}
