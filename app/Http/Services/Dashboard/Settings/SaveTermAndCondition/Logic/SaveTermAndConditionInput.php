<?php
namespace App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SaveTermAndConditionInput implements InputServiceInterface
{

    private $value_ar;
    private $value_en;

    public function __construct( array $input)
    {
        $this->value_ar = $input['value'];
        $this->value_en = $input['value_en'];

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getArValue(){
        return $this->value_ar;
    }

    public function getEnValue(){
       return $this->value_en;
    }


}