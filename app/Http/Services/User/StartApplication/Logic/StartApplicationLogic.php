<?php
namespace App\Http\Services\User\StartApplication\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class StartApplicationLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private StartApplicationInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        
        //$new_notification = 

        $myScheduledOrders = $this->repository->BookingRepository()
                ->readRepository()
                ->getScheduledBookingsForAuthUser();


        $reminderScheduledOrder =      Booking::with(['subService','driver','payment'])
        ->where('userId',Auth::user()->id)
        ->where('status',OrderStatus::$InProgress)
                ->where('is_scheduled',true)->first();

        $response  = new StartApplicationOutput([
            'myScheduledOrders'=>$myScheduledOrders,
            'reminderScheduledOrder'=>$reminderScheduledOrder
            ] , 'data channel');
        return $response->send_as_object();
   }
}