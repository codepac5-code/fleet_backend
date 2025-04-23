<?php
namespace App\Http\Services\Dashboard\PublicServices\AjaxLists\Logic;
use App\Http\Core\Response\SendResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class AjaxListsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AjaxListsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }

    public function __call($name , $arguments) {
        
        return SendResponse::send_json_response(
            new ResponseModel(
                null,
                "The list '".$name. "' is not available!"
            )
        );
    }


    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new AjaxListsOutput([] , '');
        return $response->send_as_array();
   }



   public function cities(){
    $auth_user = authSession();
    $data = $this->input->data;
    $read_repo = $this->repository->CityRepository()->readRepository();
    $stateId = isset($data['stateId']) ? $data['stateId'] : null;
    $value = isset($data['value']) ? $data['value'] : null;
    $list = $read_repo->get_cities(  $stateId , $value );
    
    return response()->json(['status' => 'true', 'results' => $list]);
   }



   // ---------- COUNTRIES LIST  -------------
   
   public function countries(){
    $data = $this->input->data;
    
    $read_repo = $this->repository->CountryRepository()->readRepository();
    $list = $read_repo->get_countries(  $value = isset($data['value']) ? $data['value'] : null);  

    return response()->json(['status' => 'true', 'results' => $list]);
}


   // ---------- STATES LIST  -------------
   
   public function states_list(){
    $data = $this->input->data;
    $read_repo = $this->repository->StateRepository()->readRepository();
    $list = $read_repo->get_states( $country = isset($data['countryId']) ? $data['countryId'] : null  , 
    $value = isset($data['value']) ? $data['value'] : null);  

    return response()->json(['status' => 'true', 'results' => $list]);
}


   // ---------- SERVECES LIST  -------------
   
   public function services_list(){
    $data = $this->input->data;
    $read_repo = $this->repository->ServiceRepository()->readRepository();
    $list = $read_repo->getAllRecords(['name as text','id']);

    return response()->json(['status' => 'true', 'results' => $list]);
}



public function drivers_list(){
    $data = $this->input->data;
    $read_repo = $this->repository->DriverRepository()->readRepository();
    $list = $read_repo->getByConditions(['officeId'=>$data['officeId']]);
    if($list == null)
    {  
        $list = [];
    }

    return response()->json(['status' => 'true', 'results' => $list]);
}

public function driver_address_list(){
    $data = $this->input->data;
    $read_repo = $this->repository->DriverAddressRepository()->readRepository();
    $list = $read_repo->getByConditions(['driverId' ,$data['driverId'] ]);

    return response()->json(['status' => 'true', 'results' => $list]);
}

public function office_address_list(){
    $data = $this->input->data;
    $read_repo = $this->repository->OfficeAddressRepository()->readRepository();
    $list = $read_repo->getByConditions(['officeId' ,$data['officeId'] ]);

    return response()->json(['status' => 'true', 'results' => $list]);
}




}