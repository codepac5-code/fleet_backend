<?php
namespace App\Http\Services\User\RattingDriver\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class RattingDriverInput implements InputServiceInterface
{
    public string $description;
    public int $orderId;
    public float $rating;

    public function __construct( array $input)
    {
        $this->description = $input['description'] ?? '';
        $this->orderId = $input['orderId'];
        $this->rating = $input['rating'];
    }

    // write your input function here..

    public function toArray(){
        return [
            'description' => $this->description,
            'orderId' => $this->orderId,
            'rating' => $this->rating,
        ];
    }

    /**
     * Get the value of rating
     */ 
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set the value of rating
     *
     * @return  self
     */ 
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }
}