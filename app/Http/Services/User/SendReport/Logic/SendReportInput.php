<?php
namespace App\Http\Services\User\SendReport\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SendReportInput implements InputServiceInterface
{
    private  $photo;
    private  $description;
    private  $subject;

    

    public function __construct($input){
        $this->photo        = $input["image"]       ?? null;
        $this->description  = $input["description"] ?? null;
        $this->subject      = $input["subject"]     ?? null;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}