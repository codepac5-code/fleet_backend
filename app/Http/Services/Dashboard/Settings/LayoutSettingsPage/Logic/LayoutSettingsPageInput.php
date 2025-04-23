<?php
namespace App\Http\Services\Dashboard\Settings\LayoutSettingsPage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class LayoutSettingsPageInput implements InputServiceInterface
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