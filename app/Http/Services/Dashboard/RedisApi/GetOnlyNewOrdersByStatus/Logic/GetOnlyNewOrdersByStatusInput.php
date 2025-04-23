<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Logic;

use App\Http\Core\InternalInterface\InputServiceInterface;

class GetOnlyNewOrdersByStatusInput implements InputServiceInterface
{
    private $lastID = 0;
    private $status;
    public function __construct( array $input)
    {
        $this->lastID = $input['last_order_id'] ?? 0;
        $this->status = $input['status'];
    }

    public function getLastId(){return $this->lastID;}
    public function getStatus(){return $this->status;}

    // write your input function here..

    public function toArray(){
        return [
            ''=>''
        ];
    }
}