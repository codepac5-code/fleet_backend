<?php
namespace App\Http\Services\User\UserAddressManagement\DeleteAddress\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DeleteAddressInput implements InputServiceInterface
{
    private $addressId;
    private $userId;



    public function __construct( Array $input
    ){
        $this->userId = $input["userId"];
        $this->addressId = $input["addressId"];
    }



    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of addressId
     */
    public function getAddressId()
    {
        return $this->addressId;
    }

    /**
     * Set the value of addressId
     *
     * @return  self
     */
    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;

        return $this;
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
}
