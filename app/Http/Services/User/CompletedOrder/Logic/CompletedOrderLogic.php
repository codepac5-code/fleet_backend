<?php
namespace App\Http\Services\User\CompletedOrder\Logic;

use Carbon\Carbon;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CompletedOrderLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CompletedOrderInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {
        
        $this->repository->BookingRepository()->updateRepository()->update(
            ['id'=>$this->input->getOrderId()],[
                'status'    => OrderStatus::$Completed ,
                'endAt'     => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );
        
        //if(){}
        $response  = new CompletedOrderOutput([] , SuccessMessages::getKey(SuccessMessages::$CommpletedOrder));
        return $response->send_as_array();
   }
}