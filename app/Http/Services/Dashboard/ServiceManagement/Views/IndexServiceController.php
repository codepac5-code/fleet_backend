<?php
namespace App\Http\Services\Dashboard\ServiceManagement\Views;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use Illuminate\Http\Request;

class IndexServiceController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..

        $filter = [
            'status' => $request->status,
        ];

        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.service')] );
        $auth_user = User::find(1);
        $assets = ['datatable'];

        return view('service.index', compact('pageTitle','auth_user','assets','filter'));
        
    }
}
