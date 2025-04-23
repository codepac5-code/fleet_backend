<?php
namespace App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateUserInput implements InputServiceInterface
{
    private $firstName;
    private $lastName;
    private $phoneNumber;
    private $gender;
    private $isRegistered;
    private $photo;
    private $walletBalance;
    private $password;
    private $id;
    private $hasImage;
    private $current_image;

    public function __construct(array $input)
    {
        $this->id            = $input['id'] ?? null;
        $this->firstName     = $input['firstName'] ?? null;
        $this->lastName      = $input['lastName'] ?? null;
        $this->phoneNumber   = $input['phoneNumber'] ?? null;
        $this->gender        = $input['gender'] ?? null;
        $this->isRegistered  = isset($input['is_registered']) ? (bool)$input['is_registered'] : false;
        $this->photo         = $input['photo'] ?? null;
        $this->walletBalance = isset($input['walletBalance']) ? (double)$input['walletBalance'] : 0;

        if (!empty($input['password'])) {
            $this->setPassword($input['password']);
        }
        $this->hasImage = $input['has_image'] ?? false ;  
        $this->current_image = $input['current_image']?? null;   
    }

    private function setPassword($password)
    {
        $this->password = hashData($password);
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }


    
    /**
     * Get the value of hasImage
     */
    public function hasImage() {
        return $this->hasImage;
    }

    /**
     * Get the value of firstName
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     */
    public function setFirstName($firstName): self {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     */
    public function setLastName($lastName): self {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get the value of phoneNumber
     */
    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    /**
     * Set the value of phoneNumber
     */
    public function setPhoneNumber($phoneNumber): self {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * Get the value of gender
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * Set the value of gender
     */
    public function setGender($gender): self {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get the value of photo
     */
    public function getPhoto() {
        return $this->photo;
    }

    /**
     * Set the value of photo
     */
    public function setPhoto($photo): self {
        $this->photo = $photo;
        return $this;
    }

    /**
     * Get the value of isRegistered
     */
    public function getIsRegistered() {
        return $this->isRegistered;
    }

    /**
     * Set the value of isRegistered
     */
    public function setIsRegistered($isRegistered): self {
        $this->isRegistered = $isRegistered;
        return $this;
    }

    /**
     * Get the value of walletBalance
     */
    public function getWalletBalance() {
        return $this->walletBalance;
    }

    /**
     * Get the value of password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }
}