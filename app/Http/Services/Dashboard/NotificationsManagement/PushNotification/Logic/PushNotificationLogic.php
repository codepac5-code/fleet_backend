<?php
namespace App\Http\Services\Dashboard\NotificationsManagement\PushNotification\Logic;

use App\Http\Core\Classes\NotificationHandler;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class PushNotificationLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private PushNotificationInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {
        

        switch($this->input->getIsType()){

            case 'user': 
                (new NotificationHandler())->send_notification_to_users(
                    new NotificationModel(
                        title_ar: $this->input->getTitle(),
                        body_ar: $this->input->getBody(),
                        title_en: $this->input->getTitleEn(),
                        body_en: $this->input->getBodyEn(),
                        image:$this->input->getImage()
                    )
                );     
                break;
            
            case 'driver' :  
                ( new NotificationHandler())->send_notification_to_drivers(
                new NotificationModel(
                    title_ar    : $this->input->getTitle(),
                    body_ar     : $this->input->getBody(),
                    title_en    : $this->input->getTitleEn(),
                    body_en     : $this->input->getBodyEn(),
                    image       : $this->input->getImage()
                )
            );  
            break;

            default : break;
        }


       
        $response  = new PushNotificationOutput([] , 'notification send successfully!');
        return $response->send_as_object();
   }
}