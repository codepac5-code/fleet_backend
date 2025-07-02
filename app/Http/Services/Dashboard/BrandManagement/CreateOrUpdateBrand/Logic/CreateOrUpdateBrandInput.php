<?php
namespace App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateBrandInput implements InputServiceInterface
{

    private $image;
    private $name;
    private $description;
    private $name_en;
    private $description_en;
    private $id;

    public function __construct( array $input)
    {
        $this->id = $input['id']??null;
        $this->name = $input['name'];
        $this->description =$input['description'] ?? null;
        $this->name_en = $input['name_en'];
        $this->description_en =$input['description_en'] ?? null;
        $this->image = $input['image'] ?? null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Get the value of name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the value of description
     */
    public function getDescription() {
        return $this->description;
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
     * Get the value of id
     */
    public function getId() {
        return $this->id;
    }
}