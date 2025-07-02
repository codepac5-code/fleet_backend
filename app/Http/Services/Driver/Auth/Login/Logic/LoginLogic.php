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


        $car = $driver->vehicle;
        if($driver->officeId != null){
            $driver['officePhone']= $this->repository->OfficeRepository()
            ->readRepository()->getByValue('id',$driver['officeId'])->contactNumber;
        }
        else{
            $driver['officePhone'] = '0937766225' ;
        }
        if($car == null){
            make_exception(__('messages.driver_has_no_car'));
        }
        $driver['car_number']= $car->plate ?? 'xxxxxxx';
        $driver['car_image']= $car->photo ??'';
        $driver['token']= getToken($driver);
        $driver ['rating'] = round($driver->rating, 1);


        $response  = new LoginOutput(
        data:$driver,
        message:  __('messages.login_successfully')
        );

        //SuccessMessages::getKey('')
        return $response->send_as_object();
   }
}
