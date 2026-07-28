<?php
namespace App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class CreateOrUpdateVehicleInput implements InputServiceInterface
{
    private $id;
    private $office_id;
    private $vehicle_brand ;
    private $plate;
    private $model_year;
    private $color;
    private $description;
    private $seats_count;
    private $sub_service_ids;
    private $stateId;
    private $city;
    private $driverId;
    private $licenseNumber;
    private $image;
    private $hasImage;
    private $model;
    public function __construct( array $input)
    {
        $this->id = $input['id'] ??  null;
        $this->office_id = $input['office_id']??null;
        $this->vehicle_brand = $input['vehicle_brand'];
        $this->plate           = $input['plate'];
        $this->model_year     =  $input['model_year'];
        $this->color          =  $input['color'];
        $this->description    = isset($input['description']) ?$input['description']:null;
        $this->seats_count    = $input['seats_count'];
        $this->sub_service_ids = $input['serviceIds'];
        $this->city        = $input['city'];
        $this->image = $input['image']?? null;
        $this->hasImage = $input['has_image'] ?? false ;
        $this->licenseNumber = $input['license_number'] ?? null;
        $this->model =$input['model'] ;
        // $this->driverId = $input['driverId'];
    }

    public function toArray(){
        return [
            ''=>''
        ];
    }


    public function hasImage() {
        return $this->hasImage;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of office_id
     */
    public function getOffice_id()
    {
        return is_null($this->office_id) ? getOfficeIdByAuthUser() :$this->office_id;
    }

    /**
     * Set the value of office_id
     *
     * @return  self
     */
    public function setOffice_id($office_id)
    {
        $this->office_id = $office_id;

        return $this;
    }

    /**
     * Get the value of vehicle_brand_id
     */
    public function getVehicle_brand()
    {
        return $this->vehicle_brand;
    }

    /**
     * Set the value of vehicle_brand_id
     *
     * @return  self
     */
    public function setVehicle_brand($vehicle_brand)
    {
        $this->vehicle_brand = $vehicle_brand;

        return $this;
    }

    /**
     * Get the value of model_year
     */
    public function getModel_year()
    {
        return $this->model_year;
    }

    /**
     * Set the value of model_year
     *
     * @return  self
     */
    public function setModel_year($model_year)
    {
        $this->model_year = $model_year;

        return $this;
    }

    /**
     * Get the value of color
     */
    public function getColor() {
        return $this->color;
    }

    /**
     * Get the value of description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Get the value of seats_count
     */
    public function getSeatsCount() {
        return $this->seats_count;
    }

    /**
     * Get the value of stateId
     */
    public function getStateId() {
        return $this->stateId;
    }

    /**
     * Get the value of plate
     */
    public function getPlate() {
        return $this->plate;
    }



    /**
     * Get the value of city
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Get the value of driverId
     */
    public function getDriverId() {
        return $this->driverId;
    }

    public function getImage() {
        return $this->image;
    }

    /**
     * Get the value of licenseNumber
     */
    public function getLicenseNumber() {
        return $this->licenseNumber;
    }

    /**
     * Set the value of licenseNumber
     */
    public function setLicenseNumber($licenseNumber): self {
        $this->licenseNumber = $licenseNumber;
        return $this;
    }

    /**
     * Get the value of sub_service_ids
     */
    public function getSubServiceIds() {
        return $this->sub_service_ids;
    }

    /**
     * Get the value of model
     */
    public function getModel() {
        return $this->model;
    }
}
