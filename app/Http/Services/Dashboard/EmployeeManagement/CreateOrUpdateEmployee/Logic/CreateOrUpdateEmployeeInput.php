<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;
use Illuminate\Support\Facades\Auth;

class CreateOrUpdateEmployeeInput implements InputServiceInterface
{
    private $id;
    private $officeId;
    private $firstName;
    private $lastName;
    private $gender;
    private $phoneNumber;
    private $email;
    private $password;
    private $country;
    private $city;
    private $region;
    private $address;
    private $profileImage;
    private $hasImage;
    private $employeeJobName_en;
    private $employeeJobName_ar;
    private $job_description_en;
    private $job_description_ar;
    private $roles;




    public function __construct(array $input)
    {
        $this->id            = $input['id'] ?? null;

        if(auth()->guard('office')->check()){
            $this->officeId      = Auth::user()->id;
        }else{
            $this->officeId      = null;
        }
        $this->firstName     = $input['firstName'] ?? null;
        $this->lastName      = $input['lastName'] ?? null;
        $this->gender        = $input['gender'] ?? null;
        $this->phoneNumber   = $input['phoneNumber'] ?? null;
        isset($input['password']) ? $this->setPassword($input['password'] ) : null;
        $this->country       = $input['country'] ?? null;
        $this->city          = $input['city'] ?? null;
        $this->region        = $input['region'] ?? null;
        $this->address       = $input['address'] ?? ' ';
        $this->profileImage  = $input['image'] ?? null;
        $this->hasImage = $input['has_image'] ?? false ;  
        $this->email = $input['email'];
        $this->employeeJobName_ar = $input['employeeJobName_ar'];
        $this->employeeJobName_en = $input['employeeJobName_en'];
        $this->job_description_ar = $input['job_description_ar'];
        $this->job_description_en = $input['job_description_en'];
        $this->roles = $input['role'];


    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

/**
     * Get the value of type
     */ 
    public function getRolesArray()
    {
        return $this->roles;
    }
        
    /**
     * Get the value of type
     */ 
    public function getEmployeeJobName_ar()
    {
        return $this->employeeJobName_ar;
    }
    
        /**
     * Get the value of type
     */ 
    public function getEmployeeJobName_en()
    {
        return $this->employeeJobName_en;
    }

        
        /**
     * Get the value of type
     */ 
    public function getJobDescription_ar()
    {
        return $this->job_description_ar;
    }

        
        /**
     * Get the value of type
     */ 
    public function getJobDescription_en()
    {
        return $this->job_description_en;
    }
    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }


       /**
     * Get the value of officeId
     */ 
    public function getOfficeId()
    {
        return $this->officeId;
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


}
