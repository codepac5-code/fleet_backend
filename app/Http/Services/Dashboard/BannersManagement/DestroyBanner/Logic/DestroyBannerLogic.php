<?php
namespace App\Http\Services\Dashboard\BannersManagement\DestroyBanner\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;

class DestroyBannerLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyBannerInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse {

        $banner = $this->repository->SliderRepository()->deleteRepository()->delete(['id' =>$this->input->getDriverId()]);
        $msg = __('messages.msg_deleted',['name' => __('messages.banner')] );
        if($banner == false ) {
            $msg = __('messages.msg_fail_to_delete',['item' => __('messages.banner')] );
            return comman_custom_response(['message'=> $msg , 'status' => false]);
        }
        return comman_custom_response(['message'=> $msg , 'status' => true]);
   }
}