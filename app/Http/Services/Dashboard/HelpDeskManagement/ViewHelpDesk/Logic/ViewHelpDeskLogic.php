<?php
namespace App\Http\Services\Dashboard\HelpDeskManagement\ViewHelpDesk\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserReport;
use Yajra\DataTables\DataTables;

class ViewHelpDeskLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewHelpDeskInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        
        $query = collect([
            'id' => 1,
            'subject' => 'مشكلة في الطابعة',
            'employee_id' => 3,
            'email' => 'employee101@example.com',
            'contact_number' => '0501234567',
            'mode' => 'هاتف',
            'description' => 'الطابعة لا تعمل بعد تحديث النظام.',
            'status' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // $query = UserReport::query();
        $filter = $this->input->getFilter();

        // if (isset($filter)) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('status', $filter['column_status']);
        //     }
        // }
        // if (auth()->user()->hasAnyRole(['super-admin'])) {
        //     $query->newquery()->withTrashed();
        // }else{
        //     $query->where('userId',auth()->user()->id);
        // }
        
        return DataTables::of($query)
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="helpdesk" onclick="dataTableRowCheck('.$row->id.',this)">';
        })
        ->editColumn('id' , function ($query){
            return '#'. $query->id;
        })
        ->editColumn('name' , function ($query){
            $user = User::first();
            return view('helpdesk.user', compact('user'));
        })
        ->filterColumn('name',function($query,$keyword){
            $query->whereHas('users',function ($q) use($keyword){
                $q->where('firstName','like','%'.$keyword.'%');
            });
        })
        ->orderColumn('name', function ($query, $order) {

            $query->select('user_reports.*')
                  ->join('users as employees', 'employees.id', '=', 'user_reports.userId')
                  ->orderBy('employees.firstName', $order);   
        })
        ->editColumn('subject', function($query){
            // if (auth()->user()->can('helpdesk edit')) {
            //     $link =  '<a class="btn-link btn-link-hover" href='.route('helpdesk.create', ['id' => $query->id]).'>'.$query->subject.'</a>';
            // } else {
            //     $link = $query->subject;
            // }
            return $query->subject;
        })
        ->editColumn('datetime' , function ($query){
            $sitesetup = Setting::where('type','site-setup')->where('key', 'site-setup')->first();
            $datetime = json_decode($sitesetup->value);
            $date = date("$datetime->date_format $datetime->time_format", strtotime($query->updated_at->setTimezone(new \DateTimeZone($datetime->time_zone ?? 'UTC'))));
            return $date;
        })
        ->editColumn('mode' , function ($query){
            return ucfirst($query->mode) ?? '-';
        })
        ->editColumn('role' , function ($query){
            return ucfirst(optional($query->users)->user_type) ?? '-';
        })
        ->editColumn('status' , function ($query){
            $status = $query->status;
            if($status == 0){
                $status = '<span class="badge text-success bg-success-subtle">'.'open'.'</span>';
            }else{
                $status = '<span class="badge text-danger bg-danger-subtle">'.'closed'.'</span>';
            }
            return $status;
        })
        ->addColumn('action', function($row){
            return view('helpdesk.action',compact('row'))->render();
        })
        ->addIndexColumn()
        ->rawColumns(['check','subject','action','status'])
            ->toJson();


        // $response  = new ViewHelpDeskOutput([] , '');
        // return $response->send_as_array();
   }
}