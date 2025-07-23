<?php
namespace App\Http\Services\SharedServices\issues\GetIssuesWithReplies\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetIssuesWithRepliesLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetIssuesWithRepliesInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

       $issues = $this->repository->IssueRepository()
       ->readRepository()
       ->getAuthUserIssues();

        $response  = new GetIssuesWithRepliesOutput( $issues , 'get Issues');
        return $response->send_as_object();
   }
}