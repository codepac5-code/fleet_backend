<?php
namespace App\Http\Services\Dashboard\RatingManagement\ShowRating\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowRatingInput implements InputServiceInterface
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