<?php
namespace App\Http\Services\Apis\StripePayment\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Services\StripeService;

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

        $response  = new StripePaymentOutput([] , $intent);
        return $response->send_as_object();
   }
}