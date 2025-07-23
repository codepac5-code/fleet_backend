<?php
namespace App\Http\Services\Driver\GetIssueDetails\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\UserReport;

class GetIssueDetailsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetIssueDetailsInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $issue = UserReport::with('replies')
        ->where('id', $this->input->getIssueId())
        ->first();

        $response  = new GetIssueDetailsOutput($issue , 'get issue details');
        return $response->send_as_object();
   }
}