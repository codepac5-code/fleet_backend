<?php
namespace App\Http\Repositories\IssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Issue;

class IssueReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Issue();
    }

    public function getAuthUserIssues($paginate = 5){
        return auth()->user()
        ->issues()
        ->latest()
        ->paginate($paginate);
    }


    public function getIssueDetailsByAuthUser($issueId){
       return auth()->user()
        ->issues()
        ->with('replies')
        ->findOrFail($issueId);    
    }
}