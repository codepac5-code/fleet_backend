<?php
namespace App\Http\Services\Driver\GetDriverNotification\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetDriverNotificationLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetDriverNotificationInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {
        
       // $driver = getAuthUser(Guard::$Driver);
        $notifications = $this->repository->DriverRepository()->readRepository()
        ->getNotifications(2);

        // $notifications = [];
        
        
        $response  = new GetDriverNotificationOutput($notifications , 'get driver notifications');
        return $response->send_as_object();
   }
}