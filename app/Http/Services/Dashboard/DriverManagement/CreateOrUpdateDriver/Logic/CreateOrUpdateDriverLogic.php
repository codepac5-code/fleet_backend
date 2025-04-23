<?php
namespace App\Http\Services\Dashboard\DriverManagement\CreateOrUpdateDriver\Logic;
use App\Http\Core\Classes\ImageManager;
use App\Http\Core\Classes\StatisticsEvent;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\RedirectResponse;

class CreateOrUpdateDriverLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateDriverInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | RedirectResponse{
        
        // if(demoUserPermission()){
        // }

        $ImageManager = new ImageManager();
        
        $data = [
            'firstName' => $this->input->getFirstName(),
            'lastName' => $this->input->getLastName(),
            'gender' => $this->input->getGender(),
            'phoneNumber' => $this->input->getPhoneNumber(),
            'officeId' => $this->input->getOfficeId(),
            'address' => $this->input->getAddress(),
            'country' => $this->input->getCountry(),
            'city' => $this->input->getCity(),
            'region' => $this->input->getRegion(),
            'vehicleId' =>$this->input->getVehicleId(),
            'photo' => $ImageManager->default_profile_photo()
        ];

        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getProfileImage(), $path = 'images/sub_service');
            $path = $ImageManager->withStorge( $path );
            $data['photo'] = $path;
        }
                
        if($this->input->getId() != null ){

            $user = $this->repository->DriverRepository()->updateRepository()->update(
                ['id'=> $this->input->getId()],
                $data 
            );

            if( $user > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.driver')]);
            }
        }
        else{

            $data['password'] = $this->input->getPassword();
            $data['is_registered'] = true;

            $user = $this->repository->DriverRepository()->createRepository()->create(
                $data
            );

            if( $user == null ){
                // $ImageManager->delete( $path);
                return  redirect()->back()->withErrors(__('messages.somethings_wrong'));
            }

            if( $user->wasRecentlyCreated ){
                $message = __( 'messages.save_form',[ 'form' => __('messages.driver') ] );
                StatisticsEvent::Drivers->send_event_to_admin(1);
            }
         
        }

		return redirect(route('driver.index'))->withSuccess($message);
   }
}