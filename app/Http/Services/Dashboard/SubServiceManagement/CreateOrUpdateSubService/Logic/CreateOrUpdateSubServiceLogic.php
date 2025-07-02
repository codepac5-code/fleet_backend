<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Core\Const\Options\Redirect;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
class CreateOrUpdateSubServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateSubServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel  |View |RedirectResponse{

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $ImageManager = new ImageManager();
        
        $data = [
            'name' => $this->input->getName(),
            'name_en' => $this->input->getNameEn(),
            'status' => $this->input->getStatus(),
            'description'=>$this->input->getDescription(),
            'description_en'=>$this->input->getDescriptionEn(),
            'openPrice'=>$this->input->getOpenPrice(),
            'kmPrice'=>$this->input->getKmPrice(),
            'minutePrice'=>$this->input->getMinutePrice(),
            'serviceId'=>$this->input->getServiceId(),
        ];

        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getImage(), $path = 'images/sub_service');
            $path = $ImageManager->withStorge( $path );
            $data['image'] = $path;
        }
                
        if($this->input->getId() != null ){

            $service = $this->repository->SubServiceRepository()->updateRepository()->update(
                ['id'=> $this->input->getId()],
                $data 
            );

            if( $service > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.subservice') ] );
            }
        }
        else{
            $service = $this->repository->SubServiceRepository()->createRepository()->create(
                $data
            );

            if( $service->wasRecentlyCreated ){
                // if ($service->travel_service) {
                //     foreach ($request->routes as $route) {
                //         TravelRoute::create([
                //             'departureCity' => $subservice->id,
                //             'arrivalCity' => $route['departureCity'],
                //             'arrivalCity' => $route['arrivalCity'],
                //             'price' => $route['tripPrice'],
                //         ]);
                //     }
                // }

                $message = __( 'messages.save_form',[ 'form' => __('messages.subservice') ] );
            }
        }

		return redirect(route('sub-service.index'))->withSuccess($message);
}

   
}