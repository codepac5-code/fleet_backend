<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\CreateOrUpdateSubService\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Core\Const\Options\Redirect;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
class CreateOrUpdateSubServiceLogic implements \App\Http\Core\InternalInterface\Service {

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
            'name'          => $this->input->getName(),
            'name_en'       => $this->input->getNameEn(),
            'status'        => $this->input->getStatus(),
            'description'   => $this->input->getDescription(),
            'description_en'=> $this->input->getDescriptionEn(),
            'openPrice'     => $this->input->getOpenPrice(),
            'kmPrice'       => $this->input->getKmPrice(),
            'minutePrice'   => $this->input->getMinutePrice(),
            'serviceId'     => $this->input->getServiceId(),
        ];

        DB::beginTransaction();


        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getImage(), $path = 'images/sub_service');
            $path = $ImageManager->withStorge( $path );
            $data['image'] = $path;
        }
        $isTravel = Service::find($this->input->getServiceId())?->travel_service;


        if($this->input->getId() != null ){

                if ($isTravel) {
                  $service=   SubService::find($this->input->getId())
                    ->updateWithRoutes($data, $this->input->getRoutes());
                } else {
                  $service =   SubService::find($this->input->getId())->updateWithRoutes($data, null);
                }
            // $service = $this->repository->SubServiceRepository()->updateRepository()->update(
            //     ['id'=> $this->input->getId()],
            //     $data
            // );

            if( $service > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.subservice') ] );
            }
        }
        else {

            // $service =   SubService::createWithRoutes($data, null);

            $service = $this->repository->SubServiceRepository()->createRepository()->createWithRoutes(
                $data,
                $isTravel? $this->input->getRoutes():null
            );
            // $service = $this->repository->SubServiceRepository()->createRepository()->create(
            //     $data
            // );


            // $this->repository->TravelRoutesRepository()
            //         ->createRepository()
            //         ->create([
            //             'departure_city'    =>$r->departureCity,
            //             'arrival_city'      =>$r->arrivalCity,
            //             'trip_price'        =>$r->tripPrice,
            //             'sub_service_id'    => $service->id,
            //         ]);

            if( $service->wasRecentlyCreated ){
                $message = __( 'messages.save_form',[ 'form' => __('messages.subservice') ] );
            }
        }

        DB::commit();

		return redirect(route('sub-service.index'))->withSuccess($message);
}


}
