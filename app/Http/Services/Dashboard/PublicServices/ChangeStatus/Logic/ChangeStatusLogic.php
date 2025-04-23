<?php
namespace App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic;
use App\Http\Core\Response\SendResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ChangeStatusLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ChangeStatusInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    )
    {
        $this->repository = new RepositoryCaller();
    }


    public function __call($name , $arguments) {
        
        return response()->json(['message' => "The '".$name. "' is not available to change status!"
    ]);
        
   
    }

    public function execute (): ResponseModel {

        // write your logic code..

        $response  = new ChangeStatusOutput([] , '');
        return $response->send_as_array();
   }


   public function service_status(){
    
    $message_form = __('messages.item');
    $message = 'can\'t update status';

    $changed = $this->repository->ServiceRepository()->updateRepository()
        ->change_status($this->input->getId() , $this->input->getStatus());

   
    if($changed ){
        $message = trans('messages.update_form', ['form' => trans('messages.status')]); }

    $message_form = __('messages.service');
    //return $service;
    return comman_custom_response(['message' => $message, 'status' => true]);
   }




   public function subcategory_status(){
    
    $message_form = __('messages.item');
    $message = 'can\'t update status';

    $changed = $this->repository->SubServiceRepository()->updateRepository()
        ->change_status($this->input->getId() , $this->input->getStatus());

   
    if($changed ){
        $message = trans('messages.update_form', ['form' => trans('messages.status')]); }

    $message_form = __('messages.service');
    //return $service;
    return comman_custom_response(['message' => $message, 'status' => true]);
   }


   

   public function banner_status(){
    
    $message_form = __('messages.item');
    $message = 'can\'t update status';

    $changed = $this->repository->SliderRepository()->updateRepository()
        ->change_status($this->input->getId() , $this->input->getStatus());

   
    if($changed ){
        $message = trans('messages.update_form', ['form' => trans('messages.status')]); }
    // $message_form = __('messages.service');
    //return $service;
    return comman_custom_response(['message' => $message, 'status' => true]);
   }
   


   

   
}