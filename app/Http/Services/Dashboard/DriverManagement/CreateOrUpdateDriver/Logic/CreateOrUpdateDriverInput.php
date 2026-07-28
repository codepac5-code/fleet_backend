<?php
namespace App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateDriverInput implements InputServiceInterface
{
    private $id;
    private $officeId;
    private $firstName;
    private $lastName;
    private $gender;
    private $phoneNumber;
    private $password;
    private $country;
    private $city;
    private $region;
    private $walletBalance;
    private $address;
    private $profileImage;
    private $input;
    private $hasImage;
    private $current_image;
    private $vehicleId;


    public function __construct(array $input)
    {
        $this->id            = $input['id'] ?? null;
        $this->officeId      = $input['officeId'] ?? null;
        $this->firstName     = $input['firstName'] ?? null;
        $this->lastName      = $input['lastName'] ?? null;
        $this->gender        = $input['gender'] ?? null;
        $this->phoneNumber   = $input['phoneNumber'] ?? null;
        isset($input['password']) ? $this->setPassword($input['password'] ) : null;
        $this->country       = $input['country'] ?? null;
        $this->city          = $input['city'] ?? null;
        $this->region        = $input['region'] ?? null;
        // $this->walletBalance = (double)$input['walletBalance'] ?? 0;
        $this->address       = $input['address'] ?? ' ';
        $this->profileImage  = $input['image'] ?? null;
        $this->hasImage = $input['has_image'] ?? false ;
        $this->current_image = $input['current_image']?? null;
        $this->vehicleId = $input['vehicleId']?? null;

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of officeId
     */
    public function getOfficeId()
    {
        return  $this->officeId;
    }

    /**
     * Set the value of officeId
     *
     * @return  self
     */
    public function setOfficeId($officeId)
    {
        $this->officeId = $officeId;

        return $this;
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

        return $this;
    }

    /**
     * Get the value of phoneNumber
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set the value of phoneNumber
     *
     * @return  self
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = hashData($password);

        return $this;
    }

    /**
     * Get the value of country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set the value of country
     *
     * @return  self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the value of city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the value of city
     *
     * @return  self
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set the value of region
     *
     * @return  self
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get the value of walletBalance
     */
    public function getWalletBalance()
    {
        return $this->walletBalance;
    }

    /**
     * Set the value of walletBalance
     *
     * @return  self
     */
    public function setWalletBalance($walletBalance)
    {
        $this->walletBalance = $walletBalance;

        return $this;
    }

    /**
     * Get the value of address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the value of profileImage
     */
    public function getProfileImage()
    {
        return $this->profileImage;
    }

    /**
     * Set the value of profileImage
     *
     * @return  self
     */
    public function setProfileImage($profileImage)
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    /**
     * Get the value of input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set the value of input
     *
     * @return  self
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of hasImage
     */
    public function hasImage() {
        return $this->hasImage;
    }

    /**
     * Get the value of current_image
     */
    public function getCurrentImage() {
        return $this->current_image;
    }

    /**
     * Get the value of vehicleId
     */
    public function getVehicleId() {
        return $this->vehicleId;
    }
}
