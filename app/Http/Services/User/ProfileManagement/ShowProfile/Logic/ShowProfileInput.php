<?php
namespace App\Http\Services\User\ProfileManagement\ShowProfile\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ShowProfileInput implements InputServiceInterface
{
    private $user;
    public function __construct( $input)
    {
        $this->user = $input;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
