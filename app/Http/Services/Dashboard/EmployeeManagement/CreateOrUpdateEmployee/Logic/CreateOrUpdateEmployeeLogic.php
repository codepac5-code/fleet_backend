<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\CreateOrUpdateEmployee\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CreateOrUpdateEmployeeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateEmployeeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // if(demoUserPermission()){
        // }

        $ImageManager = new ImageManager();
        
        $data = [
            'firstName' => $this->input->getFirstName(),
            'lastName' => $this->input->getLastName(),
            'email'=>$this->input->getEmail(),
            'gender' => $this->input->getGender(),
            'phoneNumber' => $this->input->getPhoneNumber(),
            'officeId' => $this->input->getOfficeId(),
            'address' => $this->input->getAddress(),
            'country' => $this->input->getCountry(),
            'city' => $this->input->getCity(),
            'region' => $this->input->getRegion(),
            'photo' => $ImageManager->default_profile_photo(),
            'employeeJobName_en' => $this->input->getEmployeeJobName_en(),
            'employeeJobName_ar'=> $this->input->getEmployeeJobName_ar(),
            'job_description_en'=> $this->input->getJobDescription_en(),
            'job_description_ar'=> $this->input->getEmployeeJobName_ar(),        
        ];

        if($this->input->hasImage()){
            $path = $ImageManager->upload($this->input->getProfileImage(), $path = 'images/sub_service');
            $path = $ImageManager->withStorge( $path );
            $data['photo'] = $path;
        }
                
        if($this->input->getId() != null ){

            $employee = $this->repository->EmployeeRepository()->updateRepository()->update(
                ['id'=> $this->input->getId()],
                $data 
            );

            if( $employee > 0 ){
                // if($this->input->hasImage()){
                //     $ImageManager->delete($this->input->getCurrentImage());
                // }
                $message = __('messages.update_form',[ 'form' => __('messages.employee')]);
            }
        }
        else{

            $data['password'] = $this->input->getPassword();
            $data['is_registered'] = true;

            $employee = $this->repository->EmployeeRepository()->createRepository()->create(
                $data
            );

            if( $employee == null ){
                // $ImageManager->delete( $path);
                return  redirect()->back()->withErrors(__('messages.somethings_wrong'));
            }

            if( $employee->wasRecentlyCreated ){
                $employee->assignRole($this->input->getRoleName());
                $message = __( 'messages.save_form',[ 'form' => __('messages.employee') ] );
            }
         
        }

		return redirect(route('employee.index'))->withSuccess($message);
   }
}