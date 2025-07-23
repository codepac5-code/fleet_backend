<?php
namespace App\Http\Services\User\UserHelpSuggestion\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UserHelpSuggestionInput implements InputServiceInterface
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