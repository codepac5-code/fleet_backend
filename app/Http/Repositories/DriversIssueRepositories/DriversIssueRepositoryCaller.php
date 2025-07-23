<?php
namespace App\Http\Repositories\DriversIssueRepositories;
use App\Models\{DriversIssue};

class DriversIssueRepositoryCaller{

    static public function createRepository(){return (new DriversIssueCreateRepository());}
    static public function readRepository(){return (new DriversIssueReadRepository());}
    static public function updateRepository(){return (new DriversIssueUpdateRepository());}
    static public function deleteRepository(){return (new DriversIssueDeleteRepository());}
    static public function get_model() : DriversIssue {return (new DriversIssue());}


}