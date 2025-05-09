<?php
namespace App\Http\Services\WebSite\ViewFleetLandingPage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewFleetLandingPageInput implements InputServiceInterface
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