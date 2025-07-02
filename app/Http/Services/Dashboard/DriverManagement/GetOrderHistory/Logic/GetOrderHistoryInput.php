<?php
namespace App\Http\Services\Dashboard\DriverManagement\GetOrderHistory\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetOrderHistoryInput implements InputServiceInterface
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
     * Get the value of startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }


            /**
     * Get the value of endtDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

}