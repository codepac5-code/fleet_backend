<?php
namespace App\Http\Services\Driver\Auth\Login\Logic;

use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class LoginLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private LoginInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $driverReadRepository = $this->repository->DriverRepository()->readRepository();
        $driver = $driverReadRepository->getByValue('phoneNumber' , $this->input->getPhoneNumber());

        if($driver == null)
         make_exception(__('messages.account_not_found_phoneNumber'));


         //ErrorMessages::getKey(ErrorMessages::$AccountAlreadyExists ,Attributes::Driver)


        if (!checkPassword($this->input->getPassword() , $driver->password ))
        make_exception(__('messages.incorect_password'));//ErrorMessages::getKey('')

        $driver['token']= getToken($driver);

        $car = $driver->vehicle;
        $driver['car_number']= $car->plate ?? 'xxxxxxx';
        $driver['car_image']= 'https://media.istockphoto.com/id/492362277/photo/3d-yellow-taxi.jpg?s=612x612&w=0&k=20&c=RXoWaS8t0UrqGN0cFxrbLDROw_bThdCrh-lSYjWU5L0=';

        $response  = new LoginOutput(
        data:$driver,
        message:  __('messages.login_successfully')
        );

        //SuccessMessages::getKey('')
        return $response->send_as_object();
   }
}
