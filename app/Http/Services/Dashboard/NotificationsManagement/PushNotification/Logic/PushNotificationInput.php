<?php
namespace App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class PushNotificationInput implements InputServiceInterface
{
    private $title;
    private $title_en;
    private $image;
    private $is_type;
    private $body;
    private $body_en;

    
    public function __construct( array $input)
    {
        $this->body         = $input['body_ar']     ?? null ; 
        $this->body_en      = $input['body_en']     ?? null ; 
        $this->image        = $input['image']       ?? '' ; 
        $this->is_type      = $input['is_type']     ?? null ; 
        $this->title        = $input['title_ar']       ?? null ;
        $this->title_en     = $input['title_en']    ?? null ; 
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Get the value of is_type
     */
    public function getIsType() {
        return $this->is_type;
    }

    public function getTitle() {
        return $this->title;
    }

    /**
     * Get the value of body
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Get the value of title_en
     */
    public function getTitleEn() {
        return $this->title_en;
    }

    /**
     * Get the value of body_en
     */
    public function getBodyEn() {
        return $this->body_en;
    }
}