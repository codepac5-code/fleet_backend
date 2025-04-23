<?php
namespace App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DestroyBannerInput implements InputServiceInterface
{
    private $driverId;
    public function __construct( array $input)
    {
        $this->driverId = $input['id'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of driverId
     */ 
    public function getDriverId()
    {
        return $this->driverId;
    }
}