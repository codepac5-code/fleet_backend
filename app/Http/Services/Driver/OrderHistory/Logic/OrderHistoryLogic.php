<?php
namespace App\Http\Services\Driver\OrderHistory\Logic;

use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class OrderHistoryLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private OrderHistoryInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // write your logic code..
        $orders = $this->repository->BookingRepository()->readRepository()->getAllBookingWithOrderBy(
            selected:['id','created_at','totalAmount','startAddress','startLatitude','startLongitude', 'time',
                    'endAddress','endLatitude','endLongitude','distance','paymentId','subServiceId','driverId'],
            with: [
                'driver'=>function($q){
                    return $q->select('id','rating','photo','firstName','lastName','officeDues')->get();
                },
                'subService'=>function($q){
                    return $q->select('id','name','image')->get();
                },
                'payment'=>function($q){
                    return $q->select('id','name','image')->get();
                },
            ],
            conditions: [
                'status' => OrderStatus::$Completed,
                'driverId' => $this->input->getDriverId()
            ]
        );

        $response  = new OrderHistoryOutput($orders ,
        SuccessMessages::getKey(SuccessMessages::$AccountCreated));
        return $response->send_as_object();
   }
}
