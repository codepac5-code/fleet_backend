<?php
namespace App\Http\Services\Dashboard\UsersManagement\DestroyUser\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyUserInput implements InputServiceInterface
{
    private $id;
    public function __construct( array $input)
    {
        $this->id = $input['id'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getId(){
        return $this->id;
    }
    

}