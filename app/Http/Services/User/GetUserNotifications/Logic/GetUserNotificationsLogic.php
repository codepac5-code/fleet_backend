<?php
namespace App\Http\Services\User\GetUserNotifications\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetUserNotificationsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetUserNotificationsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }



    public function execute (): ResponseModel {
        $user = getAuthUser();
        $notifications = $this->repository->UserRepository()->readRepository()
        ->getNotifications($user->id);

        $response  = new GetUserNotificationsOutput($notifications , 'get user notifications');
        return $response->send_as_object();
   }
}