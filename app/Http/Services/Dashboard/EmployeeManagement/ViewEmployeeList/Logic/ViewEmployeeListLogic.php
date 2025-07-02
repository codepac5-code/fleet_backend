<?php
namespace App\Http\Services\Dashboard\EmployeeManagement\ViewEmployeeList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Setting;
use Yajra\DataTables\DataTables;

class ViewEmployeeListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewEmployeeListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // $query = $this->repository->EmployeeRepository()->readRepository()->getAllRecords();
        // $filter = $request->filter;

        // if(auth()->user)
        $query = $this->repository->EmployeeRepository()->readRepository()
        ->dataTableEmployee( $this->input->getFilter()); 
        
        
        return DataTables::of($query)
                ->addColumn('check', function ($row) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-'.$row->id.'" name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
                })
        
                ->addColumn('full_name', function ($employee) {
                    return view('employee.employee', compact('employee'));

                    // return $row->firstName . ' ' . $row->lastName;
                })
        
                ->editColumn('phoneNumber', function ($row) {
                    return $row->phoneNumber ?? '--';
                })
        
                ->editColumn('gender', function ($row) {
                    return getGenderByLanguage($row->gender);
                })
                ->editColumn('role', function ($row) {
                    return 'ghjkn';
                })
        
                ->editColumn('isActive', function ($row) {
                    return $row->isActive ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-danger">غير نشط</span>';
                })
        
                ->editColumn('employeeJobName', function ($row) {
                    return $row->employeeJobName ?? '--';
                })
        
                ->editColumn('office', function ($row) {
                    return $row->office->name ?? textByLanguage('موظف تابع لـ فلييت' , 'fleet employee');
                })
                ->editColumn('block' , function ($query){
                    $disabled = $query->trashed() ? 'disabled': '';
                    return renderStatusSwitch($query->id , $query->isActive , 'user_status',$disabled);
                })
        
                ->editColumn('isOnline', function($query) {
                    if($query->isOnline){
                        $status = '<span class="badge badge-active">'.__('messages.online').'</span>';
                        // $status = '<a class="btn-sm text-white btn-success"  href='.route('handyman.approve',$query->id).'>Accept</a>';
    }
                else{
                        $status = '<span class="badge badge-inactive">'.__('messages.offline').'</span>';
                    }
                    return $status;
                })

                ->editColumn('address', function ($row) {
                    return $row->address ?? '--';
                })
        
                ->editColumn('created_at', function ($row) {
                    $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
                    $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
        
                    if ($datetime && isset($datetime->date_format, $datetime->time_format)) {
                        return date($datetime->date_format, strtotime($row->created_at)) . ' / ' .
                               date($datetime->time_format, strtotime($row->created_at));
                    }
        
                    return $row->created_at;
                })
        
                ->addColumn('action', function ($employee) {
                    $auth_user = authSession(); 
                    return view('employee.action', compact('employee', 'auth_user'))->render();
                })
        
                ->addIndexColumn()
                ->rawColumns(['check', 'isActive', 'action' , 'isOnline' ,'block'])
                ->make(true);
        }

        
    }