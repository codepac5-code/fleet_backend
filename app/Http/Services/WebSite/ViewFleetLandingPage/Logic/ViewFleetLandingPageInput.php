<?php
namespace App\Http\Services\WebSite\ViewFleetLandingPage\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewFleetLandingPageInput implements InputServiceInterface
{
    private $region;
    public function __construct( array $input)
    {
        $this->region = $input['region'] ?? null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }
}
