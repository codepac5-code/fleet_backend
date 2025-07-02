<?php
namespace App\Http\Services\Dashboard\BrandManagement\DestroyVBrand\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DestroyVBrandLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyVBrandInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $vbrand = $this->repository->VehicleBrandRepository()->deleteRepository()->delete(['id' =>$this->input->getBrandId()]);
        $msg = __('messages.msg_deleted',['name' => __('messages.vbrand')] );
        if($vbrand == false ) {
            $msg = __('messages.msg_fail_to_delete',['item' => __('messages.vbrand')] );
            return comman_custom_response(['message'=> $msg , 'status' => false]);
        }
        return comman_custom_response(['message'=> $msg , 'status' => true]);
   
   }
}