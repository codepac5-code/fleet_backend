<?php
namespace App\Http\Services\Dashboard\OfficeManagement\UpdateOffice\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UpdateOfficeInput implements InputServiceInterface
{
    private $id;
    private $name;
    private $email;
    private $password;
    private $country;
    private $region;
    private $city;
    private $contact_number;
    private $status;
    private $address;
    private $logo;
    private $walletBalance;
    private $limitOrders;
    private bool $has_image;

    public function __construct( array $input)
    {
        $this->id                = isset($input['id'])? $input['id'] : null;
        $this->name              = isset($input['officeName'])? $input['officeName'] : null;
        $this->email             = isset($input['email'])? $input['email'] : null;
        $this->password          = isset($input['password'])? $input['password'] : null;
        $this->country           = isset($input['country'])? $input['country'] : null;
        $this->region            = isset($input['region'])? $input['region'] : null;
        $this->contact_number    = isset($input['contact_number'])? $input['contact_number'] : null;
        $this->status            = isset($input['status'])? $input['status'] : null;
        $this->address           = isset($input['address'])? $input['address'] : null;
        $this->logo              = isset($input['logo'])? $input['logo'] : null;
        $this->city              = isset($input['city'])? $input['city'] : null;
        $this->walletBalance     = isset($input['walletBalance']) ? $input['walletBalance'] : 0.00 ;
        $this->limitOrders       = isset( $input['limitOrders'])? $input['limitOrders'] : 0;
        $this->has_image         = $input['has_image'] ?? false;

    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Get the value of password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Get the value of country
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Get the value of city
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Get the value of contact_number
     */
    public function getContactNumber() {
        return $this->contact_number;
    }

    /**
     * Get the value of status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the value of address
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Get the value of logo
     */
    public function getLogo() {
        return $this->logo;
    }

    /**
     * Get the value of walletBalance
     */
    public function getWalletBalance() {
        return $this->walletBalance;
    }

    /**
     * Get the value of limitOrders
     */
    public function getLimitOrders() {
        return $this->limitOrders;
    }

    /**
     * Get the value of has_image
     *
     * @return bool
     */
    public function hasImage(): bool {
        return $this->has_image;
    }

    /**
     * Get the value of name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get the value of region
     */
    public function getRegion() {
        return $this->region;
    }
}