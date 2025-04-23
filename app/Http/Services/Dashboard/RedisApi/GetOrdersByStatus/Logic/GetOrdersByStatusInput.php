<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetOrdersByStatusInput implements InputServiceInterface
{
    
    private $page;
    private $status;
    public function __construct( array $input)
    {
        $this->page = $input['page'] ?? 1;
        $this->status = $input['status'];
    }

    public function getPage(){return $this->page;}
    public function getStatus(){return $this->status;}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}