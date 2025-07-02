<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ViewVehicleByOffice\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewVehicleByOfficeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewVehicleByOfficeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {
        
        // $vehicles = $this->repository->VehicleRepository()
        // ->readRepository()
        // ->getByConditions(['officeId'=>$this->input->getOfficeId()]);

        $office = $this->repository->OfficeRepository()
        ->readRepository()->find($this->input->getOfficeId());

        return view('office.vehicleByOffice',compact(['office']));
        $response  = new ViewVehicleByOfficeOutput([] , '');
        return $response->send_as_array();
   }
}