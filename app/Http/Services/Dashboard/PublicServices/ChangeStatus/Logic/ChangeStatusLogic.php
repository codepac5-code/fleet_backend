<?php
namespace App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic;
use App\Http\Core\Response\SendResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\GeoServices\ShardAggregator;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;

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

    /**
     * The service/sub-service/banner catalogs are per-country: in the aggregate
     * "All countries" scope they are exposed as UNION views (not updatable), and
     * a catalog id repeats across shards so a status flip would be ambiguous.
     * Refuse the write with a clear instruction rather than crash on the view.
     * Returns a response to short-circuit with, or null to proceed.
     */
    private function aggregateGuard() {
        // Single-country scope: the `dynamic` connection already points at the
        // right shard — write normally.
        if (! ShardAggregator::isActive()) {
            return null;
        }

        // Aggregate "All countries": the catalog tables are non-updatable UNION
        // views and an id repeats across shards. The row carried its owning
        // country (`_shard` → data-country); repoint `dynamic` at that real shard
        // so the write lands in exactly one country. No shard given → refuse
        // rather than write blind.
        $country = request('country');

        if ($country !== null && $country !== '' && $country !== ShardAggregator::SCOPE) {
            $node = InfrastructureNode::find((int) $country);

            if ($node) {
                ShardManager::activate($node);
                app()->instance('shard_all', false);

                return null;
            }
        }

        return comman_custom_response([
            'message' => textByLanguage(
                'تعذّر تحديد الدولة لهذا العنصر — افتح دولة محددة ثم غيّر الحالة.',
                'Could not resolve this item’s country — open a specific country, then change the status.'
            ),
            'status' => false,
        ]);
    }


   public function service_status(){

    if ($guard = $this->aggregateGuard()) { return $guard; }

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

    if ($guard = $this->aggregateGuard()) { return $guard; }

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

    if ($guard = $this->aggregateGuard()) { return $guard; }

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