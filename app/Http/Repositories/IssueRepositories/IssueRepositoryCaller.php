<?php
namespace App\Http\Repositories\IssueRepositories;
use App\Models\{Issue};

class IssueRepositoryCaller{

    static public function createRepository(){return (new IssueCreateRepository());}
    static public function readRepository(){return (new IssueReadRepository());}
    static public function updateRepository(){return (new IssueUpdateRepository());}
    static public function deleteRepository(){return (new IssueDeleteRepository());}
    static public function get_model() : Issue {return (new Issue());}


}