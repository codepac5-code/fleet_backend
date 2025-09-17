<?php
namespace App\Http\Services\Driver\Profile\GetProfile\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Livewire\Features\SupportQueryString\BaseUrl;

class GetProfileLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetProfileInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){}


    public function execute (): ResponseModel {

        // write your logic code..

        //$photoUrl =  env('APP_URL') .$this->input->getDriver();
        $data = $this->input->getDriver();
        $data['ScheduledOrdersCount'] = 3;
        $response  = new GetProfileOutput(
            $data , '');
        return $response->send_as_object();
   }
}
