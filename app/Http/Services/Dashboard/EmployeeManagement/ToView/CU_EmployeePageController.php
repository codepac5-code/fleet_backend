<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\ToView;

use App\Models\User;
use App\Models\Driver;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Roles;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\Vehicle;

class CU_EmployeePageController extends Controller
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $employee = Driver::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.employee')]);

        if($employee == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.employee')]);
            $employee = new Driver;
        }

        $offices = Office::all(); 
        $countries = Country::all(); 
        $cities = City::all(); 

        $repository = new RepositoryCaller();
        $roles = Role::where(['status'=>true])->where('name','!=','super-admin')->get();

        return view('employee.create', compact('pageTitle' ,'roles' ,'auth_user' , 'offices','cities','countries'));
    }
}
