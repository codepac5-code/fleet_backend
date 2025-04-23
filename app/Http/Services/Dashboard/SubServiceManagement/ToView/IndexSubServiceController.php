<?php
namespace App\Http\Services\Dashboard\SubServiceManagement\ToView;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;

class IndexSubServiceController extends Controller
{
    public function __invoke(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.subservice')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('sub-service.index',compact('pageTitle','auth_user','assets','filter'))->render();
    }
}
