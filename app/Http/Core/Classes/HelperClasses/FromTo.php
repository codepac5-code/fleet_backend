<?php
namespace App\Http\Core\Classes\HelperClasses;


class FromTo {

    private $from_value;
    private $to_value;


    public function __construct($from_value ,  $to_value){
        $this->from_value = $from_value;
        $this->to_value = $to_value;
    }
    
    public function setFromValue($value){
         $this->from_value = $value;
    }

    public function setToValue($value){
         $this->to_value = $value;
    }


    public function getFromValue(){
        return $this->from_value;
    }

    public function getToValue(){
        return $this->to_value;
    }

    
}