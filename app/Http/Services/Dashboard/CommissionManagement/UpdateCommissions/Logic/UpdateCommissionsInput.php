<?php
namespace App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class UpdateCommissionsInput implements InputServiceInterface
{
    private $fleet_commission_value_with_driver;
    private $fleet_commission_value_with_office;
    private $office_commission;
    private $driver_commission;
    private $type;

    public function __construct( array $input)
    {
        $this->type= $input['type'];
        $this->fleet_commission_value_with_driver = $input['fleet_driver'] ?? null;
        $this->fleet_commission_value_with_office = $input['fleet_office'] ?? null;
        $this->office_commission = $input['office_commission'] ?? null;
        $this->driver_commission = $input['driver_commission'] ?? null;
        
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }


    /**
     * Get the value of type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get the value of fleet_commission_value_with_driver
     */
    public function getFleetCommissionValueWithDriver() {
        return $this->fleet_commission_value_with_driver;
    }

    /**
     * Get the value of fleet_commission_value_with_office
     */
    public function getFleetCommissionValueWithOffice() {
        return $this->fleet_commission_value_with_office;
    }

    /**
     * Get the value of office_commission
     */
    public function getOfficeCommission() {
        return $this->office_commission;
    }

    /**
     * Get the value of driver_commission
     */
    public function getDriverCommission() {
        return $this->driver_commission;
    }
}