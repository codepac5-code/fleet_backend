<?php
namespace App\Http\Services\Dashboard\ServiceManagement\CreateOrUpdateService\Logic;

use App\Http\Core\Classes\ImageManager;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\RedirectResponse;

class CreateOrUpdateServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }
    
    public function execute (): ResponseModel | RedirectResponse{

                // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $ImageManager = new ImageManager();
        
        $data = [ 
            'title'=>$this->input->getName(),
            'title_en'=>$this->input->getNameEn(),
            'status' =>$this->input->getStatus(),
            'description'=>$this->input->getDescription(),
            'description_en'=>$this->input->getDescriptionEn(),
            'travel_service'=>$this->input->getTravelService(),
        ];

        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getImage(), $path = 'images/service');
            $path = $ImageManager->withStorge( $path );
            $data['image'] = $path;
        }
                
        if($this->input->getId() != null ){

            $service = $this->repository->ServiceRepository()->updateRepository()->update(
                ['id'=> $this->input->getId()],
                $data 
            );

            if( $service > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.service') ] );
            }
        }
        else{
            
            $service = $this->repository->ServiceRepository()->createRepository()->create(
                $data
            );


            if( $service->wasRecentlyCreated ){    
                $message = __( 'messages.save_form',[ 'form' => __('messages.service') ] );
            }
            else{
                
            }
        }

		return redirect(route('service.index'))->withSuccess($message);
   }
}