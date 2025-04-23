<?php
namespace App\Http\Services\User\GetPaymentMethod\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetPaymentMethodLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
            //---------------------------------------------------------------------------------------
             private GetPaymentMethodInput $input /*| Pass Request To Service */
            //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $select = select_by_language([
            'name',
            'image',
            'status',
            'type',
        ] , [
            'name_en as name',
            'image',
            'status',
            'type',
        ]);


        $paymentMethodRepository =  $this->repository->PaymentMethodRepository()->readRepository();

        if($this->input->isWalletCharge()){
            $paymentMethod = $paymentMethodRepository->getWhereIn( 
                'type', [ 'mtn','syriatel'],
            $select);

        }else{
            $paymentMethod = $paymentMethodRepository->getAllRecords( $select);

        }



        $response  = new GetPaymentMethodOutput($paymentMethod , 'get payment methods');
        return $response->send_as_object();
   }
}
