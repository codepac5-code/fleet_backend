<?php
namespace App\Http\Services\User\GetReferralMessage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetReferralMessageInput implements InputServiceInterface
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