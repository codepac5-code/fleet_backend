<?php
namespace App\Http\Services\Dashboard\DriverManagement\Views;

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
use App\Models\Vehicle;

class CU_DriverPageController extends Controller
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $driver = Driver::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.driver')]);

        if($driver == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.driver')]);
            $driver = new Driver;
        }

        $offices = Office::all(); 
        $countries = Country::all(); 
        $cities = City::all(); 

        $repository = new RepositoryCaller();
        if(auth()->user()->hasAnyRole([Roles::Super_Admin->value])){
            $vehicles = $repository->VehicleRepository()->readRepository()->getAllRecords();
           }elseif(auth()->user()->hasAnyRole([Roles::Office->value]))
           {
            $vehicles = $repository->VehicleRepository()
            ->readRepository()
            ->getByConditions(['officeId'=>auth()->user()->id]);
           }else{
            $vehicles = new Vehicle();
           }

        return view('driver.create', compact('pageTitle' ,'vehicles','driver' ,'auth_user' , 'offices','cities','countries'));
    }
}
