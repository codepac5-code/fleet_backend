<?php
namespace App\Http\Services\User\OrderHistory\Logic;

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
                    'endAddress','endLatitude','endLongitude','distance','paymentId','subServiceId','driverId','multiDestnationArray'],
            with: [
                'driver'=>function($q){
                    return $q->select('id','rating','photo','firstName','lastName')->get();
                },
                'subService'=>function($q){
                    $select = select_by_language(['id','name','image'] , 
                    ['id','image','name_en as name' ]);
                    return $q->select( $select)->get();
                },
                'payment'=>function($q){
                    $select = select_by_language(['id','name','image'] , 
                        ['id','image','name_en as name' ]);
                    return $q->select($select)->get();
                },
            ],
            conditions: [
                'status' => OrderStatus::$Completed,
                'userId' => $this->input->getUserId()
            ]
        );

        $response  = new OrderHistoryOutput($orders ,
        SuccessMessages::getKey(SuccessMessages::$AccountCreated));

        return $response->send_as_object();
   }
}
