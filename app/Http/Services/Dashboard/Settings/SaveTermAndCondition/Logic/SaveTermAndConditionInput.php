<?php
namespace App\Http\Services\Dashboard\Settings\SaveTermAndCondition\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SaveTermAndConditionInput implements InputServiceInterface
{

    private $value;
    private $id;
    public function __construct( array $input)
    {
        $this->value = $input['value'];
        $this->id    = $input['id'];

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of value
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }
}