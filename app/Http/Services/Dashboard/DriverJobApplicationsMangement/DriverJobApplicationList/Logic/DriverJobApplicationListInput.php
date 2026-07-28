<?php
namespace App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class DriverJobApplicationListInput implements InputServiceInterface
{
    private $from_date;
    private $to_date;
    private $status;
    public function __construct( array $input)
    {
        $this->from_date = $input["from_date"] ?? null ;
        $this->to_date = $input["to_date"] ?? null ;
        $this->status = $input["status"] ?? null ;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of from_date
     */
    public function getFrom_date()
    {
        return $this->from_date;
    }

    /**
     * Get the value of to_date
     */
    public function getTo_date()
    {
        return $this->to_date;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
