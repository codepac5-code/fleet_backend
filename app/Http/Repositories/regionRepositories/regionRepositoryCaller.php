<?php
namespace App\Http\Repositories\regionRepositories;
use App\Models\{region};

class regionRepositoryCaller{

    static public function createRepository(){return (new regionCreateRepository());}
    static public function readRepository(){return (new regionReadRepository());}
    static public function updateRepository(){return (new regionUpdateRepository());}
    static public function deleteRepository(){return (new regionDeleteRepository());}
    static public function get_model() : region {return (new region());}


}