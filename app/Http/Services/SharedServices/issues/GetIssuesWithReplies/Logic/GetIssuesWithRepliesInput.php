<?php
namespace App\Http\Services\SharedServices\issues\GetIssuesWithReplies\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetIssuesWithRepliesInput implements InputServiceInterface
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