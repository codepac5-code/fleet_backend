<?php
namespace App\Http\Services\User\GetSubService\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetSubServiceInput implements InputServiceInterface
{
    private int $serviceId;
    private string $start;
    private string $destination;
    private float $kmEst;
    private float $timeEst;

    public function __construct( array $input)
    {
        $this->serviceId = $input['serviceId'];
        $this->start = $input['start'];
        $this->destination = $input['destination'];
        $this->kmEst = $input['kmEst'];
        $this->timeEst = $input['timeEst'];
    }

    // write your input function here..

    public function getServiceId(){
        return  $this->serviceId;
    }

    public function getStart(){
        return  $this->start;
    }

    public function getDestination(){
        return  $this->destination;
    }

    public function getKmEst(){
        return  $this->kmEst;
    }

    public function getTimeEst(){
        return  $this->timeEst;
    }

    public function toArray(){
        return [

        ];
    }
}
