<?php
namespace App\Http\Services\WebSite\GetPrivacyPolicyPage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetPrivacyPolicyPageInput implements InputServiceInterface
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