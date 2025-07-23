<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\AddNewRole\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class AddNewRoleLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AddNewRoleInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        if(demoUserPermission()){
            return response()->json(['status'=>false,'message'=>trans('messages.demo_permission_denied'),'event'=>'validation']);
        }
        $page = 'role';
        $auth_user = authSession();

        $admin_role =  $this->repository->RoleRepository()->createRepository()
        ->create([
            'name' => $this->input->getName() ,
            'status' => '1'
        ]);

        if(!$admin_role ){
            redirect()->back()->withErrors(__('messages.something_wrong'));
        }
          
        $message = trans('messages.added_role',['name'=> trans('messages.role') ] );
        return response()->json(['status' => true,'event' => 'refresh' , 'message' => $message]);
    
    //     $response  = new AddNewRoleOutput([] , '');
    //     return $response->send_as_array();
   }
}