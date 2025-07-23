<?php
namespace App\Http\Services\Dashboard\HelpDeskManagement\ToView;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Issue;
use Illuminate\Support\Facades\DB;

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

        $departments = Department::select('id', 'name_ar as name')->get();

        $agents = Employee::where('isActive', 1)
        ->select('id', DB::raw("CONCAT(firstName, ' ', lastName) as name"))
        ->get();

        $statuses = Issue::select('status')
            ->distinct()
            ->pluck('status');


        $priorities = Issue::select('priority')->distinct()->pluck('priority');
        // return response()->json([
        //     'departments' => $departments,
        //     'agents' => $agents,
        //     'statuses' => $statuses,
        // ]);
    
    
        return view('helpdesk.index', compact('pageTitle','auth_user','departments','agents','statuses'));
    }

}
