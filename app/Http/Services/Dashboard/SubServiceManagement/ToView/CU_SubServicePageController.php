<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\ToView;

use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\Service;
use App\Models\SubService;

class CU_SubServicePageController extends Controller
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();
    
        $subservice = null;
        if($id != null){    
            $subservice = SubService::find($id);
          }
        
        $pageTitle = trans('messages.update_form_title',['form'=>trans('messages.subservice')]);
        
        if($subservice == null){
            $pageTitle = trans('messages.add_button_form',['form' => trans('messages.subservice')]);
            $subservice = new SubService();
        }
    
         $services = Service::where(['status'=> true])->get();
        
         $cities = City::all();
        return view('sub-service.create', compact('services','pageTitle' ,'subservice' ,'auth_user','cities' ));
    }
}
