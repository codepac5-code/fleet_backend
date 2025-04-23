<?php
namespace App\Http\Repositories\AdminRepositories;

class AdminRepositoryCaller{

    static public function createRepository(){return (new AdminCreateRepository());}
    static public function readRepository(){return (new AdminReadRepository());}
    static public function updateRepository(){return (new AdminUpdateRepository());}
    static public function deleteRepository(){return (new AdminDeleteRepository());}

}