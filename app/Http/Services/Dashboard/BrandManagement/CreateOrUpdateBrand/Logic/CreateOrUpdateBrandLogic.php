<?php
namespace App\Http\Services\Dashboard\BrandManagement\CreateOrUpdateBrand\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CreateOrUpdateBrandLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateBrandInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

       // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $ImageManager = new ImageManager();
        
        if($this->input->getImage() != null){
             $path = $ImageManager->upload($this->input->getImage(), $path = 'vbrands');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = $ImageManager->default_photo();
        }

        if($this->input->getId()!= null ){
            
            $vbrand = $this->repository->VehicleBrandRepository()->createRepository()
            ->updateOrCreate(
              ['id'=>  $this->input->getId()] , 
            [
              'name' => $this->input->getName(),
              'description' => $this->input->getDescription(),
              'name_en' => $this->input->getNameEn(),
              'description_en' => $this->input->getDescriptionEn(),
              'image' => $path
          ]);
        
        } else{

            $vbrand = $this->repository->VehicleBrandRepository()->createRepository()
            ->create([
              'name' => $this->input->getName(),
              'description' => $this->input->getDescription(),
              'image' => $path,
              'name_en' => $this->input->getNameEn(),
              'description_en' => $this->input->getDescriptionEn(),
            ]);
        }

       
    
        if( $vbrand == null ){
            $ImageManager->delete( $path);
            return  redirect()->back()->withErrors(trans("حدث خطأ ما يرجى اعادة المحاولة"));
        }
        
        // $vbrand->assignRole('vbrand');

        $message = __('messages.update_form',[ 'form' => __('messages.the_vbrand') ] );
		if( $vbrand->wasRecentlyCreated ){
			$message = __( 'messages.save_form',[ 'form' => __('messages.the_vbrand') ] );
		}
		return redirect(route('vbrand.index'))->withSuccess($message);

        // $response  = new CreateOrUpdateBrandOutput([] , '');
        // return $response->send_as_array();

   }


   
}