<?php
namespace App\Http\Core\Const\Messages;

class ErrorMessages{

    static public $AccountAlreadyExists = "account_already_exists";
    static public $SomeThingWentWrong = 'some_thing_went_wrong';
    static public $invalidData = 'invalid data';
    static public $endOtp = 'endOtp';
    static public $notExait = "not_exait";
    static public $sendSuccess = "send success";
    static public $wrongPassword = "wrong_password";

    static public $permsionDenied = "permsion_denied";
    static public $gendorError = "gendorError";

    static public $dbError = "database_error";
    static public $incorrectPassword = "incorrect_password";




    static public function addSuccess ($attr) {
        return  "add " . $attr . " successfully";
    }

    static public function showAllSuccess ($attr) {
        return  "show all " . $attr . " successfully";
    }

    static public function deleteSuccess ($attr) {
        return  "delete " . $attr . " successfully";
    }

    static public function updateSuccess ($attr) {
        return  "update " . $attr . " successfully";
    }

    static function getKey(string $key ,Attributes $attribute = Attributes::None){
        return $attribute->value.":messages." . $key;
    }



}

