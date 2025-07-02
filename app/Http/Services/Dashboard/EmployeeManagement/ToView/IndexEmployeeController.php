<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\ToView;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;

class IndexEmployeeController extends Controller
{
    public function __invoke(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title',['form' => __('messages.employee')] );

        $auth_user = authSession();
        $assets = ['datatable'];
        $list_status = $request->status;
        return view('employee.index', compact('list_status','pageTitle','auth_user','assets','filter'));
    }
}
