<?php
namespace App\Http\Services\User\RattingDriver\Logic;

use App\Http\Core\Classes\NotificationsSenderClasses\RattingNotifications;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class RattingDriverLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private RattingDriverInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        $order = $this->repository->BookingRepository()->readRepository()->find( 
            $this->input->orderId,);

        $order->rating = $this->input->rating;
        $order->save();

        $data = $this->input->toArray();

        /// retar
        $data['rater_id'] = $order->userId;
        $data['rater_type'] = get_class($this->repository->UserRepository()->get_model());

        // rated person
        $data['rated_person_id'] = $order->driverId;
        $data['rated_person_type'] = get_class($this->repository->DriverRepository()->get_model());


        $this->repository->RatingRepository()->createRepository()->create($data);

        $driverRepository = $this->repository->DriverRepository();

        $driver = $driverRepository->readRepository()->find($order->driverId);
        
        $driverRepository->updateRepository()->update(
            ['id' => $driver->id ],
            [ 'rating' => ($driver->rating + $this->input->rating)/2]  
        );

        $driverRepository->readRepository()->notifyDriver($driver->id, RattingNotifications::new_review_notification($this->input->rating ,$order->id));


        $response  = new RattingDriverOutput([] , 
        __('messages.driver_rated', ['rating' => $this->input->getRating()]));

        return $response->send_as_object();
   }
}