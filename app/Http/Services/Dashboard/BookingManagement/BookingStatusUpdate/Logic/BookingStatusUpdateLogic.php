<?php
namespace App\Http\Services\Dashboard\BookingManagement\BookingStatusUpdate\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class BookingStatusUpdateLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private BookingStatusUpdateInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $order = $this->repository->BookingRepository();
        switch ($this->input->getType()) {
            case 'payment':
                $data = $order->updateRepository()->update(['id'=> $this->input->getBookingId()], ['paymentStatus'=>$this->input->getStatus()]);
                break;

                default:
                $data = $order->updateRepository()->update(['id'=> $this->input->getBookingId()], ['status'=>$this->input->getStatus()]);
                break;
        }

        return comman_custom_response(['message'=> 'Status Updated' , 'status' => true]);

   }
}