<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\Role_Layout_Page\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Admin;
use App\Models\ParentPermission;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class Role_Layout_PageLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private Role_Layout_PageInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $tabpage = $this->input->getTabpage();
        $auth_user = getAuthUser();
        $user_id = $auth_user->id;
        $user_data = Admin::find($user_id);
       
        switch ($tabpage) {
            case 'role':
                
                $filter = [
                    'status' => $this->input->getStatus(),
                ];
                $pageTitle = trans('messages.list_form_title',['form' => trans('messages.role')] );
                $auth_user = authSession();
                $assets = ['datatable'];
                $data  = view('role.index', compact('user_data','tabpage','pageTitle','auth_user','assets','filter'))->render();
                break;

            case 'permission':
                // $permission = Permission::with(['subpermission' => function($query) {
                //     $query->orderBy('name');
                // }])
                // ->whereNull('parent_id')
                // ->orderBy('name','ASC')
                // ->get();  


                $permission = ParentPermission::get()->unique('name')->values();//with('permissions')->get();
                
                // $pp = Permission::whereNull('parent_id')->get();
               
                // $permission = Permission::orderBy('name','ASC')->get();

                $pageTitle = trans('messages.list_form_title',['form' => trans('messages.permission')  ]);
        
                // $roles = Role::all();
                $roles = Role::where('status',1)->orderBy('name','ASC')->where('name','!=','super-admin')->get();

                // if(!Auth::user()->hasRole('super-admin')){
                // }
                // $roles = $roles->get();
        
                $auth_user = authSession();
                //  return response()->json($auth_user);
                // return response()->json(['sssssssssss0']);

                $data  = view('permission.index', compact('user_data','tabpage','roles','permission','pageTitle','auth_user'))->render();
                break;
            default:
                $data  = view('role.index',compact('user_data','tabpage','pageTitle','auth_user','assets','filter'))->render();
                break;
        }
        return response()->json($data);
   }
}