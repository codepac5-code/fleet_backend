<?php
namespace App\Http\Services\Driver\RattingUser\Logic;

use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class RattingUserLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories


    public function __construct(
    //---------------------------------------------------------------------------------------
    private RattingUserInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {


        // write your logic code..
        $order = $this->repository->BookingRepository()->readRepository()->find(
            $this->input->orderId
        );

        $data = $this->input->toArray();
        $data['userId'] = $order->userId;
        
        /// retar
        $data['rater_id'] = $order->driverId;
        $data['rater_type'] = get_class($this->repository->DriverRepository()->get_model());

        // rated person
        $data['rated_person_id'] = $order->userId;
        $data['rated_person_type'] = get_class($this->repository->UserRepository()->get_model());


        $this->repository->RatingRepository()->createRepository()->create($data);


        $response  = new RattingUserOutput([] , __('messages.passenger_rated', ['rating' => $this->input->getRating()]) );
        return $response->send_as_object();
   }
}