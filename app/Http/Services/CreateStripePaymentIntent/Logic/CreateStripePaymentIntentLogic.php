<?php
namespace App\Http\Services\CreateStripePaymentIntent\Logic;
use App\Http\Core\Classes\Integration\Stripe\StripeService;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CreateStripePaymentIntentLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateStripePaymentIntentInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {


        $stripe_service = new StripeService();

        $intent = $stripe_service->createPaymentIntent($this->input->getAmount());

        if($intent->status !== "success"){
            info(message: 'stripe intent error');
            make_exception(__('messages.something_wrong'));
        }

        if($this->input->getOrderId() != null){
            $this->repository->BookingRepository()->updateRepository()
            ->update(['id'=>$this->input->getOrderId()],
            ['stripe_payment_intent_id'=>$intent->id]);
        }


        $response  = new CreateStripePaymentIntentOutput([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
                'customer_id' => $intent->customer,
                'ephemeralKey' => $intent->ephemeralKey,
            ]  , 'stripe payment intent  created successfully!');
        return $response->send_as_object();
   }
}
