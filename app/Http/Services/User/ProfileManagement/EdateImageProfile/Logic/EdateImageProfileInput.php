<?php
namespace App\Http\Services\User\ProfileManagement\EdateImageProfile\Logic;

use App\Http\Core\Const\Options\GendorOptions;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\InternalInterface\InputServiceInterface;

class EdateImageProfileInput implements InputServiceInterface
{

    private  $photo;
    private int $userId;

    public function __construct($input){
        $this->photo = $input["photo"] ?? null;
        $this->userId = $input['userId'];
    }


    public function toArray(){
        return
        [
            'id' =>$this->userId,
            'photo'  =>$this->photo,
        ];
    }



    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of photo
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set the value of photo
     *
     * @return  self
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }
}
