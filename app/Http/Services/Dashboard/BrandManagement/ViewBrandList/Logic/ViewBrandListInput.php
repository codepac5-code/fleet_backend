<?php
namespace App\Http\Services\Dashboard\BrandManagement\ViewBrandList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewBrandListInput implements InputServiceInterface
{
    public function __construct( array $input)
    {}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}