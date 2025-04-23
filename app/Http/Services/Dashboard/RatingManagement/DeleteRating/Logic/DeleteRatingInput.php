<?php
namespace App\Http\Services\Dashboard\RatingManagement\DeleteRating\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeleteRatingInput implements InputServiceInterface
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