<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;
use App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Request\CreateOrUpdateSubServiceRequest;

class CreateOrUpdateSubServiceInput implements InputServiceInterface
{


    private $id;
    private $image;
    private $name;
    private $status;
    private  $name_en;
    private  $description_en;
    private $description;
    private $openPrice;
    private $kmPrice;
    private $minutePrice;
    private $serviceId;
    private $hasImage;
    private $current_image;

    public function __construct( array $input)
    {
        $this->id          = isset($input['id'])? $input['id'] : null;
        $this->name        = isset($input['name']) ? $input['name'] : null;
        $this->name_en        = isset($input['name_en']) ? $input['name_en'] : null;
        $this->status      = isset($input['status']) ? $input['status'] : null;
        $this->description = isset($input['description']) ? $input['description'] : null;
        $this->description_en = isset($input['description_en']) ? $input['description_en'] : null;
        $this->openPrice   = isset($input['openPrice']) ? $input['openPrice'] : null;
        $this->kmPrice     = isset($input['kmPrice']) ? $input['kmPrice'] : null;
        $this->minutePrice = isset($input['minutePrice']) ? $input['minutePrice'] : null;
        $this->serviceId   = isset($input['serviceId']) ? $input['serviceId'] : null;
        //$this->serviceId   = isset($input['serviceId']) ? $input['serviceId']: null;
        $this->image = $input['image'] ?? null;
        $this->hasImage = $input['has_image'] ?? false ;  
        $this->current_image = $input['current_image'];    
    }

    public function toArray(){
        return [
            ''=>''
        ];
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
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of kmPrice
     */ 
    public function getKmPrice()
    {
        return $this->kmPrice;
    }

    /**
     * Set the value of kmPrice
     *
     * @return  self
     */ 
    public function setKmPrice($kmPrice)
    {
        $this->kmPrice = $kmPrice;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of openPrice
     */ 
    public function getOpenPrice()
    {
        return $this->openPrice;
    }

    /**
     * Set the value of openPrice
     *
     * @return  self
     */ 
    public function setOpenPrice($openPrice)
    {
        $this->openPrice = $openPrice;

        return $this;
    }

    /**
     * Get the value of minutePrice
     */ 
    public function getMinutePrice()
    {
        return $this->minutePrice;
    }

    /**
     * Set the value of minutePrice
     *
     * @return  self
     */ 
    public function setMinutePrice($minutePrice)
    {
        $this->minutePrice = $minutePrice;

        return $this;
    }

    /**
     * Get the value of serviceId
     */ 
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set the value of serviceId
     *
     * @return  self
     */ 
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

 

    /**
     * Get the value of hasImage
     */
    public function hasImage() {
        return $this->hasImage;
    }

    /**
     * Get the value of image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Get the value of current_image
     */
    public function getCurrentImage() {
        return $this->current_image;
    }

    /**
     * Get the value of name_en
     */
    public function getNameEn() {
        return $this->name_en;
    }

    /**
     * Get the value of description_en
     */
    public function getDescriptionEn() {
        return $this->description_en;
    }
}