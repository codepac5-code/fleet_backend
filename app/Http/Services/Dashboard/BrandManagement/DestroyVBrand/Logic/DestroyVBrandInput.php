<?php
namespace App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyVBrandInput implements InputServiceInterface
{
    private $brandId;
    public function __construct( array $input)
    {
        $this->brandId = $input['vbrandId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of brandId
     */ 
    public function getBrandId()
    {
        return $this->brandId;
    }
}