<?php
namespace App\Http\Services\Dashboard\Transactions\ViewPayments\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class ViewPaymentsInput implements InputServiceInterface
{
    private $officeId ;
    public function __construct( array $input)
    {
        $this->officeId = $input['officeId']?? null;
    }

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}