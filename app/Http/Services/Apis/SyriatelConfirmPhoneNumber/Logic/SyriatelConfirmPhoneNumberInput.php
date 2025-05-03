<?php
namespace App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class SyriatelConfirmPhoneNumberInput implements InputServiceInterface
{
    private $invoiceId ;
    private int $operationNumber;
    private  $code;

    public function __construct( array $input)
    {
        $this->invoiceId = $input['invoiceId'];
        $this->code = $input['code'];

    }

    // write your input function here..

    public function toArray(){
        return [
            'invoiceId' => $this->getInvoiceId(),
            'operationNumber' => $this->getOperationNumber(),
            'code' => $this->getCode()
        ];
    }

    /**
     * Get the value of InvoiceId
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * Set the value of InvoiceId
     *
     * @return  self
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * Get the value of operationNumber
     */
    public function getOperationNumber()
    {
        return $this->operationNumber;
    }

    /**
     * Set the value of operationNumber
     *
     * @return  self
     */
    public function setOperationNumber($operationNumber)
    {
        $this->operationNumber = $operationNumber;

        return $this;
    }

    /**
     * Get the value of code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}