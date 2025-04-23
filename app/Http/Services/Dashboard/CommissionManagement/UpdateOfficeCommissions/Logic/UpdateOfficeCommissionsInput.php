<?php
namespace App\Http\Services\Dashboard\CommissionManagement\UpdateOfficeCommissions\Logic;
use App\Http\Core\InternalInterface\InputServiceInterface;

class UpdateOfficeCommissionsInput implements InputServiceInterface
{
    private $commission_with_office_car;
    private $commission_with_driver_car;
    private $driver_commission_precentage;
    private $driver_car_commission_precentage;
    private $type;

    public function __construct( array $input)
    {
        $this->commission_with_office_car = $input['commission_with_office_car'] ?? null;
        $this->commission_with_driver_car = $input['commission_with_driver_car'] ?? null;
        $this->driver_commission_precentage = $input['driver_commission'] ?? null;
        $this->driver_car_commission_precentage = $input['driver_car_commission'] ?? null;
        $this->type = $input['type'];
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }

    /**
     * Get the value of commission_with_office_car
     */
    public function getCommissionWithOfficeCar() {
        return $this->commission_with_office_car;
    }

    /**
     * Get the value of commission_with_driver_car
     */
    public function getCommissionWithDriverCar() {
        return $this->commission_with_driver_car;
    }

    /**
     * Get the value of driver_commission_precentage
     */
    public function getDriverCommissionPrecentage() {
        return $this->driver_commission_precentage;
    }

    /**
     * Get the value of driver_car_commission_precentage
     */
    public function getDriverCarCommissionPrecentage() {
        return $this->driver_car_commission_precentage;
    }

    /**
     * Get the value of type
     */
    public function getType() {
        return $this->type;
    }
}