<?php
namespace App\Http\Services\Apis\StripePayment\Logic;
use App\Http\Core\Classes\Integration\Stripe\StripeService;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class StripePaymentLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private StripePaymentInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $stripe_service = new StripeService();

        $intent = $stripe_service->createPaymentIntent($this->input->getAmount());

                if($intent->status !== "success"){
            info('stripe intent error');
        }
        $order = $this->repository->BookingRepository()->
        readRepository()->find($this->input->getOrderId());

        $message = $stripe_service->getPaymentStatusMessage($order);

        //         $order = $this->repository->BookingRepository()->
        // readRepository()->find($this->input->getOrderId());

        if($order->status  != 'success'){
            info('stripe intent error');
            make_exception('error payment not pending');
        }



        $response  = new StripePaymentOutput([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'status' => 'success'
            ] , $message);

        return $response->send_as_object();
   }
}
