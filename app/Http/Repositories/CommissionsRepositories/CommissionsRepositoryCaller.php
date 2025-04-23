<?php
namespace App\Http\Repositories\CommissionsRepositories;
use App\Models\{Commissions};

class CommissionsRepositoryCaller{

    static public function createRepository(){return (new CommissionsCreateRepository());}
    static public function readRepository(){return (new CommissionsReadRepository());}
    static public function updateRepository(){return (new CommissionsUpdateRepository());}
    static public function deleteRepository(){return (new CommissionsDeleteRepository());}
    static public function get_model() : Commissions {return (new Commissions());}


}