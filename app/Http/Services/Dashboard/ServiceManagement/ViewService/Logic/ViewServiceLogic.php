<?php
namespace App\Http\Services\Dashboard\ServiceManagement\ViewService\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class ViewServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }

    public function execute (): ResponseModel |JsonResponse {

        // write your logic code..
        $read_repo = $this->repository
        ->ServiceRepository()->readRepository();
        $servics = $read_repo->getDatatableServices( $this->input->getFilter()); 


        return  DataTables::of($servics)
        ->addColumn('action', function ($service) {
            return view('service.action', compact('service'))->render();
        })
        ->editColumn('name', function ($row) {
            $link = '<a class="btn-link btn-link-hover" href=' . route('service.create', ['id' => $row->id]) . '>' . $row->name . '</a>';
            return $link;
        })
        ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" data-type="service" onclick="dataTableRowCheck(' . $row->id . ',this)">';
        })
        ->editColumn('image', function ($data) {
            return view('service.datatable-card', compact('data'));
        })
        ->editColumn('status', function ($query) {
            $disabled = $query->trashed() ? 'disabled' : '';
            // `_shard` is present only in the aggregate "All countries" view; it
            // routes the toggle to the row's own country shard (the union view is
            // not updatable). Null in a single-country context.
            return renderStatusSwitch($query->id, $query->status, 'service_status', $disabled, $query->_shard ?? null);
        })
        ->rawColumns(['name', 'action', 'image', 'status', 'check', 'description'])
        ->toJson();
   }
}