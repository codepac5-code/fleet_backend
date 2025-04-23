<?php
namespace App\Http\Services\User\ProfileManagement\EditeProfile\Logic;

use App\Http\Core\Const\Options\GendorOptions;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\InternalInterface\InputServiceInterface;

class EditeProfileInput implements InputServiceInterface
{
    private int $userId;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $gender;



    public function __construct($input){
        $this->userId    = isset($input["userId"]) ? $input["userId"] : null ;
        $this->firstName = isset($input["firstName"]) ? $input["firstName"] : null ;
        $this->lastName  = isset($input["lastName"]) ? $input["lastName"] : null;
        $this->gender    = isset($input["gender"]) ? $input["gender"] : null;
    }

    public function toArray(){
        return
        [
            'firstName' =>$this->firstName,
            'lastName'  =>$this->lastName,
            'gender'    =>$this->getGender(),
        ];
    }



    /**
     * Get the value of firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @return  self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @return  self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of gender
     */
    public function getGender()
    {
        // if ($this->gender && !in_array($this->gender,GendorOptions::$gendor)) {
        //     make_exception(ErrorMessages::getKey(ErrorMessages::$gendorError));
        // }
        return $this->gender;
    }

    /**
     * Set the value of gender
     *
     * @return  self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

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
