<?php
namespace App\Http\Services\Driver\Earning\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class EarningInput implements InputServiceInterface
{
    public ?string $startDate;
    public ?string $endDate;
    private ?int $drivereId;
    public function __construct( array $input)
    {
        $this->startDate = $input['startDate'];
        $this->endDate = $input['endDate'] ;
        $this->drivereId = $input['driverId'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of drivereId
     */
    public function getDrivereId()
    {
        return $this->drivereId;
    }

    /**
     * Set the value of drivereId
     *
     * @return  self
     */
    public function setDrivereId($drivereId)
    {
        $this->drivereId = $drivereId;

        return $this;
    }
}
