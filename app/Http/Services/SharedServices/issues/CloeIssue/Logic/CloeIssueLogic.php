<?php
namespace App\Http\Services\SharedServices\issues\CloeIssue\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CloeIssueLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CloeIssueInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $issue = $this->repository->IssueRepository()
        ->readRepository()
        ->find($this->input->getIssueId());
        
        if (!$issue) {
            make_exception(__('messages.ticket_not_found'));
        }
        
        if ($issue->isClosed) {
            make_exception(__('messages.ticket_already_closed'));
        }

        $this->repository->IssueRepository()
        ->updateRepository()
        ->update(
            ['id'=>$issue->id],
            [
                'isClosed' => true,
                'closedAt' => now(),
                // 'status' => 'closed',
            ]
        );

        $response  = new CloeIssueOutput([] , 'issue closed successfully!');
        return $response->send_as_object();
   }
}