<?php
namespace App\Http\Repositories\IssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Issue;

class IssueCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Issue();
    }


    public function createNewIssueForAuthUser($subject,$description,$photo)
    {
        $issue = auth()->user()->issues()->create([
            'subject' => $subject,
            'description' => $description,
            'photo' => $photo,
        ]);
    }


  
}