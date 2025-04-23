<?php
namespace App\Http\Services\Dashboard\BookingManagement\AddBooking\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class AddBookingLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct (
    //---------------------------------------------------------------------------------------
    private AddBookingInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new AddBookingOutput([] , '');
        return $response->send_as_array();
   }
}
