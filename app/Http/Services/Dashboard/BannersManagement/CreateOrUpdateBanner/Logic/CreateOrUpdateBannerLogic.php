<?php
namespace App\Http\Services\Dashboard\BannersManagement\CreateOrUpdateBanner\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\RedirectResponse;

class CreateOrUpdateBannerLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateBannerInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | RedirectResponse {

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $ImageManager = new ImageManager();
        
        if($this->input->getImage() != null){
             $path = $ImageManager->upload($this->input->getImage(), $path = 'banners');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = $ImageManager->default_photo();
        }

        if($this->input->getId()!= null ){
            
            $banner = $this->repository->SliderRepository()->createRepository()
            ->updateOrCreate(
              ['id'=>  $this->input->getId()     ] , 
            [
              'title' => $this->input->getTitle(),
              'description' => $this->input->getDescription(),
              'title_en' => $this->input->getTitleEn(),
              'description_en' => $this->input->getDescriptionEn(),
              'image' => $path
          ]);
        
        } else{

            $banner = $this->repository->SliderRepository()->createRepository()
            ->create([
              'title' => $this->input->getTitle(),
              'description' => $this->input->getDescription(),
              'image' => $path,
              'title_en' => $this->input->getTitleEn(),
              'description_en' => $this->input->getDescriptionEn(),
            ]);
        }

       
    
            
        if( $banner == null ){
            $ImageManager->delete( $path);
            return  redirect()->back()->withErrors(trans("حدث خطأ ما يرجى اعادة المحاولة"));
        }
        
        // $banner->assignRole('banner');

        $message = __('messages.update_form',[ 'form' => __('messages.the_banner') ] );
		if( $banner->wasRecentlyCreated ){
			$message = __( 'messages.save_form',[ 'form' => __('messages.the_banner') ] );
		}
		return redirect(route('banner.index'))->withSuccess($message);

   }
}