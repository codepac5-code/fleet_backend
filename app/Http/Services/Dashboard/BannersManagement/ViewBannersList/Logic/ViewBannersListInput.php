<?php
namespace App\Http\Services\Dashboard\BannersManagement\ViewBannersList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewBannersListInput implements InputServiceInterface
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