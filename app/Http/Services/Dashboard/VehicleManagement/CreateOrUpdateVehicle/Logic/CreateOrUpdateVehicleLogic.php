<?php
namespace App\Http\Services\Dashboard\VehicleManagement\CreateOrUpdateVehicle\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Models\City;
use App\Models\State;
use App\Models\Driver;
use App\Models\Office;
use App\Http\Core\Resp;
use App\Models\SubService;
use App\Models\VehicleBrand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\VehicleManagement\Pages\CreateVehiclePage\Controller\CreateVehiclePageController;

class CreateOrUpdateVehicleLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateVehicleInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | RedirectResponse{ 
        
        $ImageManager = new ImageManager();
        
        beginTransaction();
        $data = 
        [
            'officeId'          =>$this->input->getOffice_id(), 
            'vehicleBrand'      =>$this->input->getVehicle_brand(), 
            'plate'             =>$this->input->getPlate(), 
            'modelYear'         =>$this->input->getModel_year(), 
            'licenseNumber'     =>$this->input->getLicenseNumber(), 
            'model'             =>$this->input->getModel(),
        //  'lastDriver'        =>$this->input->getLast(), 
            'color'             =>$this->input->getColor(), 
        //  'driverId'          =>$this->input->getDriverId(), 
            'city'              =>$this->input->getCity(),
            'description'       =>$this->input->getDescription(),  
            'seatsCount'        =>$this->input->getSeatsCount(),   
        ];

        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getImage(), $path = 'images/vehicle/photo');
            $path = $ImageManager->withStorge( $path );
            $data['photo'] = $path;
        }
        
        $vehicle_repo  = $this->repository->VehicleRepository();

        if($this->input->getId() != null ){

            $id = $this->input->getId();
            $vehicle = $vehicle_repo->updateRepository()
            ->update( ['id'=> $id], $data);
            $vehicleId = $id;

            if( $vehicle > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.vehicle') ] );
            }
        }
    else
        {
            $vehicle = $vehicle_repo->createRepository()
            ->create( $data);
            $vehicleId = $vehicle->id;

            if($vehicle == null){
                rollbackTransaction();
                $ImageManager->delete($path);
                return  redirect()->back()->withErrors(__('messages.somethings_wrong'));
            }

            $message = __( 'messages.save_form',[ 'form' => __('messages.vehicle') ] );
        }

        $this->repository->VehicleRepository()
        ->createRepository()->addVehicleSubServices( $vehicleId , $this->input->getSubServiceIds());
        

        commitTransaction();
		return redirect(route('vehicle.index'))->withSuccess($message);        

    }


}