<?php
namespace App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateServiceInput implements InputServiceInterface
{
    private  $id ;
    private  $userId ;
    private  $name ;
    private  $name_en;
    private  $description;
    private  $description_en;
    private  $image ;
    private  $status;
    private $hasImage;
    private $current_image;
    private $travelService;
    public function __construct( array $input)
    {
      $this->userId = isset($input['userId'] )  ? $input['userId']   : null;
      $this->name   = isset($input['name'] )    ? $input['name']     : null;
      $this->name_en   = isset($input['name_en'] )    ? $input['name_en']     : null;
      $this->image  = isset($input['image'] )   ? $input['image']    : null;
      $this->status = isset($input['status'] )  ? $input['status']    : true;
      $this->description  = isset($input['description'] )   ? $input['description'] : null;
      $this->description_en  = $input['description_en'] ?? null;
      $this->id        =    isset($input['id'] ) ? $input['id']    : null;
      $this->hasImage = $input['has_image'] ?? false ;  
      $this->current_image = $input['current_image'];    
      $this->travelService = $input['travel_service'] ?? false;
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
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of image
     */ 
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */ 
    public function setImage($image)
    {
        $this->image = $image;

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
     * Get the value of hasImage
     */
    public function hasImage() {
        return $this->hasImage;
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

    /**
     * Get the value of travelService
     */ 
    public function getTravelService()
    {
        return $this->travelService;
    }
}