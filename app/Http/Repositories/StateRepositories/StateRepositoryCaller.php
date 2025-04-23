<?php
namespace App\Http\Repositories\StateRepositories;

class StateRepositoryCaller{

    static public function createRepository(){return (new StateCreateRepository());}
    static public function readRepository(){return (new StateReadRepository());}
    static public function updateRepository(){return (new StateUpdateRepository());}
    static public function deleteRepository(){return (new StateDeleteRepository());}

}