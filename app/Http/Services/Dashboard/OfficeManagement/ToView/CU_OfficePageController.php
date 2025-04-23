<?php
namespace App\Http\Services\Dashboard\OfficeManagement\ToView;

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

class CU_OfficePageController extends Controller
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $officedata = Office::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.provider')]);

        if($officedata == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.provider')]);
            $officedata = new Office;
        }

        $countries = Country::all();
        $cities = City::all();
        return view('office.create', compact('pageTitle' ,'officedata' ,'auth_user' ,'cities' , 'countries'));
    }
}
