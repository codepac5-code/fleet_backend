<?php
namespace App\Http\Services\User\UserAddressManagement\ShowAddress\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowAddressInput implements InputServiceInterface
{
    private $userId;

    public function __construct( Array $input
    ){
        $this->userId = $input["userId"];
    }
    // write your input function here..

    public function toArray(){
        return [
            ''=>''
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
}
