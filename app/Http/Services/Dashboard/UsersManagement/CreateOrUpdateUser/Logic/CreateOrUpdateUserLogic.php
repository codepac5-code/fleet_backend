<?php
namespace App\Http\Services\Dashboard\UsersManagement\CreateOrUpdateUser\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Core\Classes\StatisticsEvent;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;


class CreateOrUpdateUserLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateUserInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View |RedirectResponse {

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        $ImageManager = new ImageManager();
        
        $data = [
            'firstName' => $this->input->getFirstName(),
            'lastName' => $this->input->getLastName(),
            'gender' => $this->input->getGender(),
            'phoneNumber' => $this->input->getPhoneNumber(),
            'photo' => $ImageManager->default_profile_photo()
        ];

        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getPhoto(), $path = 'images/user');
            $path = $ImageManager->withStorge( $path );
            $data['photo'] = $path;
        }
                
        if($this->input->getId() != null ){

            $user = $this->repository->UserRepository()->updateRepository()->update(
                ['id'=> $this->input->getId()],
                $data 
            );

            if( $user > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.user') ] );
            }
        }
        else{

            $data['password'] = $this->input->getPassword();
            $data['is_registered'] = true;

            $user = $this->repository->UserRepository()->createRepository()->create(
                $data
            );

            if( $user == null ){
                // $ImageManager->delete( $path);
                return  redirect()->back()->withErrors(__('messages.somethings_wrong'));
            }

            if( $user->wasRecentlyCreated ){
                $message = __( 'messages.save_form',[ 'form' => __('messages.user') ] );
                StatisticsEvent::Users->send_event_to_admin(1);
            }
         
        }

		return redirect(route('user.index'))->withSuccess($message);

   }
}