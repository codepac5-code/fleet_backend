<?php
namespace App\Http\Services\Driver\DriverCloseIssue\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\UserReport;

class DriverCloseIssueLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DriverCloseIssueInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $issue = UserReport::find($this->input->getIssueId());

        if (!$issue) {
            make_exception('التيكت غير موجود.');
        }

        if ($issue->isClosed) {
            make_exception('التيكت مغلق مسبقًا.');
        }

        $issue->isClosed = true;
        $issue->closedAt = now();
        // $issue->status = 'closed'; 
        $issue->save();

        $response  = new DriverCloseIssueOutput([] , 'issue closed successfully!');
        return $response->send_as_object();
   }
}