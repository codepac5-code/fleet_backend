<?php
namespace App\Http\Services\Dashboard\BookingManagement\Follow\FollowOrderOnMapToView\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class FollowOrderOnMapToViewLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private FollowOrderOnMapToViewInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $order  = $this->repository->BookingRepository()
        ->readRepository()
        ->find($this->input->getOrderId());

        $subservice = $order->subService;
        $driver = $order->driver;
        $car = $driver->vehicle;

        // return response()->json($car);
        return view('booking.follow.map' , compact('order' ,'subservice','driver','car'));
   }
}