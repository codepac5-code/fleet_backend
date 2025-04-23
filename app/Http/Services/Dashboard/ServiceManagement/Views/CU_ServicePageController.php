<?php
namespace App\Http\Services\Dashboard\ServiceManagement\Views;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Service;

class CU_ServicePageController extends Controller
{
    public function __invoke (Request $request )
    {

        $id = $request->id;
        $auth_user = authSession();

        $service = Service::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.service')]);

        if($service == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.service')]);
            $service = new Service;
        }

        return view('service.create', compact('pageTitle' ,'service' ,'auth_user' ));

    }
}
