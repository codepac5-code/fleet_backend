<?php
namespace App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateBannerInput implements InputServiceInterface
{
    private $image;
    private $title;
    private $description;
    private $title_en;
    private $description_en;
    private $id;
    public function __construct( array $input)
    {
        $this->id = $input['id']??null;
        $this->title = $input['title'];
        $this->description =$input['description'] ?? null;
        $this->title_en = $input['title_en'];
        $this->description_en =$input['description_en'] ?? null;
        $this->image = $input['image'] ?? null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getImage(){return $this->image;}
    public function getDescription(){return $this->description;}
    public function getTitle(){return $this->title;}
    public function getId(){return $this->id;}


    /**
     * Get the value of title_en
     */
    public function getTitleEn() {
        return $this->title_en;
    }

    /**
     * Get the value of description_en
     */
    public function getDescriptionEn() {
        return $this->description_en;
    }
}