<?php
namespace App\Http\Repositories\DriverRepliesIssueRepositories;
use App\Models\{DriverRepliesIssue};

class DriverRepliesIssueRepositoryCaller{

    static public function createRepository(){return (new DriverRepliesIssueCreateRepository());}
    static public function readRepository(){return (new DriverRepliesIssueReadRepository());}
    static public function updateRepository(){return (new DriverRepliesIssueUpdateRepository());}
    static public function deleteRepository(){return (new DriverRepliesIssueDeleteRepository());}
    static public function get_model() : DriverRepliesIssue {return (new DriverRepliesIssue());}


}