<?php
namespace App\Http\Repositories\UserReportRepositories;
use App\Models\{UserReport};

class UserReportRepositoryCaller{

    static public function createRepository(){return (new UserReportCreateRepository());}
    static public function readRepository(){return (new UserReportReadRepository());}
    static public function updateRepository(){return (new UserReportUpdateRepository());}
    static public function deleteRepository(){return (new UserReportDeleteRepository());}
    static public function get_model() : UserReport {return (new UserReport());}


}