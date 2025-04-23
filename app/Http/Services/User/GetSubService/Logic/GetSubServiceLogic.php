<?php
namespace App\Http\Services\User\GetSubService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetSubServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetSubServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    )
    {
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        $select = select_by_language([
            'id',
            'name',
            'image',
            'status',
            'description',
            'openPrice',
            'kmPrice',
            'minutePrice',
            'serviceId',
        ] , [
            'id',
            'image',
            'status',
            'openPrice',
            'kmPrice',
            'minutePrice',
            'serviceId',
            'name_en as name',
            'description_en as description'
        ]);

        $subServiceRepositiory = $this->repository->SubServiceRepository()->readRepository();
        $subService = $subServiceRepositiory->getByConditions([
            'serviceId' => $this->input->getServiceId(),
            'status'=>true
        ],$select);

        $paymentRepositiory = $this->repository->PaymentMethodRepository()->readRepository();
        $paymentMethod = $paymentRepositiory->getAllRecords();

        foreach ($subService as $value) {
            $value->priceEst =intval($value->openPrice + ($value->kmPrice * $this->input->getKmEst()) + ($value->minutePrice * $this->input->getTimeEst()));
        }

        $response  = new GetSubServiceOutput([
            "km" => $this->input->getKmEst(),
            "time" => $this->input->getTimeEst(),
            "subService" => $subService,
            "paymentMethod" => $paymentMethod
            ] , '');
        return $response->send_as_object();
   }
}
