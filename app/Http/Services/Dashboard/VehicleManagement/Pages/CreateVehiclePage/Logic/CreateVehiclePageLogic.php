<?php
namespace App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Logic;

use App\Http\Core\Const\Options\Colors;
use Illuminate\Contracts\View\View;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;


class CreateVehiclePageLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateVehiclePageInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | View {

        $id = $this->input->getId();
    
        $auth_user = authSession();
   
       if($id != null){
           $vehicledata = $this->repository->VehicleRepository()->readRepository()
           ->find($id);
       }
       else
       {
        $vehicledata = $this->repository->VehicleRepository()->get_model();
       }
   
       $pageTitle = __('messages.update_form_title',['form'=> __('messages.vehicle')]);
   
       
       if($vehicledata == null ){
           $pageTitle = __('messages.add_button_form',['form' => __('messages.vehicle')]);
           }
   
   
       $offices = $this->repository->OfficeRepository()->readRepository()->getAllRecords();
       $vehicleBrands = $this->repository->VehicleBrandRepository()->readRepository()->getAllRecords();
       $cities = $this->repository->CityRepository()->readRepository()->getAllRecords();
       $subServices = $this->repository->SubServiceRepository()->readRepository()->getAllRecords();
       $drivers = $this->repository->DriverRepository()->readRepository()->getAllRecords();
       $colors = Colors::get_colors();
       return view('vehicle.create', compact(
           'offices', 'vehicleBrands', 'cities',  'subServices','drivers',
           'pageTitle' ,'vehicledata' ,'auth_user','colors' ));
        
   }
}