<?php
namespace App\Http\Repositories\IssueLogRepositories;
use App\Models\{IssueLog};

class IssueLogRepositoryCaller{

    static public function createRepository(){return (new IssueLogCreateRepository());}
    static public function readRepository(){return (new IssueLogReadRepository());}
    static public function updateRepository(){return (new IssueLogUpdateRepository());}
    static public function deleteRepository(){return (new IssueLogDeleteRepository());}
    static public function get_model() : IssueLog {return (new IssueLog());}


}