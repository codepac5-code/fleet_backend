<?php
namespace App\Http\Services\Driver\GetIssuesWithReplies\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\DriverRepliesIssue;
use App\Models\DriversIssue;
use App\Models\UserReport;

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

        $user = getAuthUser();
        $reports = $user->load('issues.replies');

        // UserReport::with('replies')
        // ->where('userId', $user->id)
        // ->orderBy('created_at', 'desc')
        // ->paginate(4);

        $response  = new GetIssuesWithRepliesOutput($reports , 'get driver issues with replies');
        return $response->send_as_object();
   }
}