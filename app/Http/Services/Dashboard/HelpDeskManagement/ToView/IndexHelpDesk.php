<?php
namespace App\Http\Services\Dashboard\HelpDeskManagement\ToView;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;

class IndexHelpDesk 
{
    public function __invoke(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = trans('messages.helpdesk');
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('helpdesk.index', compact('pageTitle','auth_user','assets','filter'));
    }
}
