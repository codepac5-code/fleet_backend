<?php
namespace App\Http\Services\Dashboard\UsersManagement\ToView;

use App\Models\User;
use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\City;
use App\Models\Country;

class CU_UserPageController extends Controller
{
    public function __invoke (Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $userData = User::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.user')]);

        if($userData == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.user')]);
            $userData = new user;
        }

        return view('customer.create', compact('pageTitle' ,'userData' ,'auth_user' ));
    }
}
