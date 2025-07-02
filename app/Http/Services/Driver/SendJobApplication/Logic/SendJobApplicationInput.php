<?php
namespace App\Http\Services\Driver\SendJobApplication\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SendJobApplicationInput implements InputServiceInterface
{
        private string $name;
        private string $phone;
        private string $password;
        private string $confirmPassword;
    
        // Files
        private $profileImage;       
        private $idFrontImage;
        private $idBackImage;
        private $licenseFrontImage;
        private $licenseBackImage;
        private $mechanicalImage;
    
        // private string $office;
        private string $brand;
        private string $model;
        private string $year;
        private string $color;
        private string $plateNumber;
    
        private $frontCarImage;
        private $backCarImage;
        private $rightCarImage;
        private $leftCarImage;
        private $insideCarImage;
        private $frontSeatsImage;
        private $backSeatsImage;
    
        private $optionalVideo;
    
        public function __construct(array $input)
        {
            $this->name = $input['name'];
            $this->phone = $input['phone'];
            $this->password = $input['password'];
            $this->confirmPassword = $input['confirmPassword'];
    
            $this->profileImage = $input['profileImage'];
            $this->idFrontImage = $input['idFrontImage'];
            $this->idBackImage = $input['idBackImage'];
            $this->licenseFrontImage = $input['licenseFrontImage'];
            $this->licenseBackImage = $input['licenseBackImage'];
            $this->mechanicalImage = $input['mechanicalImage'];
    
            // $this->office = $input['office'];
            $this->brand = $input['brand'];
            $this->model = $input['model'];
            $this->year = $input['year'];
            $this->color = $input['color'];
            $this->plateNumber = $input['plateNumber'];
    
            $this->frontCarImage = $input['frontCarImage'];
            $this->backCarImage = $input['backCarImage'];
            $this->rightCarImage = $input['rightCarImage'];
            $this->leftCarImage = $input['leftCarImage'];
            $this->insideCarImage = $input['insideCarImage'];
            $this->frontSeatsImage = $input['frontSeatsImage'];
            $this->backSeatsImage = $input['backSeatsImage'];
    
            $this->optionalVideo = $input['optionalVideo'] ?? null;
        }
    
        public function getName(): string { return $this->name; }
        public function getPhone(): string { return $this->phone; }
        public function getPassword(): string { return $this->password; }
        public function getConfirmPassword(): string { return $this->confirmPassword; }
        public function getProfileImage() { return $this->profileImage; }
        public function getIdFrontImage() { return $this->idFrontImage; }
        public function getIdBackImage() { return $this->idBackImage; }
        public function getLicenseFrontImage() { return $this->licenseFrontImage; }
        public function getLicenseBackImage() { return $this->licenseBackImage; }
        public function getMechanicalImage() { return $this->mechanicalImage; }
        // public function getOffice(): string { return $this->office; }
        public function getBrand(): string { return $this->brand; }
        public function getModel(): string { return $this->model; }
        public function getYear(): string { return $this->year; }
        public function getColor(): string { return $this->color; }
        public function getPlateNumber(): string { return $this->plateNumber; }
        public function getFrontCarImage() { return $this->frontCarImage; }
        public function getBackCarImage() { return $this->backCarImage; }
        public function getRightCarImage() { return $this->rightCarImage; }
        public function getLeftCarImage() { return $this->leftCarImage; }
        public function getInsideCarImage() { return $this->insideCarImage; }
        public function getFrontSeatsImage() { return $this->frontSeatsImage; }
        public function getBackSeatsImage() { return $this->backSeatsImage; }
        public function getOptionalVideo() { return $this->optionalVideo; }
    

    public function toArray(){
        return [
            ''=>''
        ];
    }
}