<?php
namespace App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Models\DriverJobApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DriverJobApplicationListLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DriverJobApplicationListInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

    $query = DriverJobApplication::query();

    // تطبيق الفلترة
    // if ($request->status) {
    //     $query->where('status', $request->status);
    // }

    // if ($request->office) {
    //     $query->where('officeId', $request->office);
    // }

    if ($this->input->getFrom_date() != null) {
        $query->whereDate('created_at', '>=', $this->input->getFrom_date());
    }

    if ($this->input->getTo_date() != null) {
        $query->whereDate('created_at', '<=', $this->input->getTo_date());
    }

    return datatables()->of($query)
        ->addColumn('check', function($application) {
            return '<input type="checkbox" name="ids[]" value="'.$application->id.'">';
        })
        ->addColumn('action', function($application) {
            // كود الأكشن
        })
        ->rawColumns(['check', 'action'])
        ->make(true);

   }
}
