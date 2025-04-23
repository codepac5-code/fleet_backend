<?php
namespace App\Http\Services\Dashboard\PublicServices\AjaxLists\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class AjaxListsInput implements InputServiceInterface
{

    public function __construct( public $data)
    {
    }

    

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }


}