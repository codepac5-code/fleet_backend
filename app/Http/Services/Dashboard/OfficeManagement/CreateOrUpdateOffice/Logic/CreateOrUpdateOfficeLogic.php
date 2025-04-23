<?php
namespace App\Http\Services\Dashboard\OfficeManagement\CreateOrUpdateOffice\Logic;

use App\Http\Core\Classes\ImageManager;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\RedirectResponse;

class CreateOrUpdateOfficeLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateOfficeInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel | RedirectResponse {

        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        $this->repository->officeRepository()->readRepository()->is_exists([]);
        
        $ImageManager = new ImageManager();
        
        if($this->input->getLogo() != null){
             $path = $ImageManager->upload($this->input->getLogo(), $path = 'office/logo');
             $path = $ImageManager->withStorge( $path );
        }
        else {
            $path = get_default_image('office');
        }

        $office_repo  = $this->repository->OfficeRepository();

        beginTransaction();

        $data = 
        [
            "email"         =>$this->input->getEmail(),
            "password"      =>$this->input->hashPassword(),
            "country"       =>$this->input->getCountry(),
            "region"        =>$this->input->getRegion(),
            "city"          =>$this->input->getCity(),
            "contactNumber" =>$this->input->getContactNumber(),
            // "status"        =>$this->input->getStatus(),
            "address"       =>$this->input->getAddress(),
            "officeName"    =>$this->input->getName(),
            'limitOrders'   =>$this->input->getLimitOrders(),
            'walletBalance' =>$this->input->getWalletBalance(),
            'logo'          =>$path
        ];
        
    

         $id = $this->input->getId();
        if( $id  != null)
        {
            $id = $this->input->getId();
            $office = $office_repo->createRepository()
            ->updateOrCreate( ['id'=> $id], $data);
        }
        else{
            $office = $office_repo->createRepository()
            ->create( $data);
        }

        if($office == null){
            rollbackTransaction();
            $ImageManager->delete($path);
            return redirect()->back()->withErrors('Something went wrong. Please try again!');
        }
       
        // if($data['status'] == 1 && auth()->user()->hasAnyRole(['admin'])){
        //     try {
        //         \Mail::send('verification.verification_email',
        //         array(), function($message) use ($user)
        //         {
        //             $message->from(env('MAIL_FROM_ADDRESS'));
        //             $message->to($user->email);
        //         });
        //     } catch (\Throwable $th) {
        //     }
        // }
        $office->assignRole('office');
     
        commitTransaction();

        $message = __('messages.update_form',[ 'form' => __('messages.office') ] );
		if( $office->wasRecentlyCreated ){
			$message = __( 'messages.save_form',[ 'form' => __('messages.office') ] );
		}

		return redirect(route('office.index'))->withSuccess($message);

   }


//    public function get_view() {
//     $id = $this->input->getId();
//     $auth_user = authSession();

//     $read_repo = $this->repository->OfficeRepository()->readRepository();
//     if($id != null){
//         $offaceData = $read_repo->find($id);

//     }
    

//     $pageTitle = __('messages.update_form_title',['form'=> __('messages.office')]);

//     // if($officedata == null){
//     //     $pageTitle = __('messages.add_button_form',['form' => __('messages.office')]);
//     //     $offaceData = new User;
//     // }

//     return view('provider.create', compact('pageTitle' ,'providerdata' ,'auth_user' ));
//    }
}