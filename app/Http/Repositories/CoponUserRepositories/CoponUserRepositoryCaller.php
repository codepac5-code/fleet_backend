<?php
namespace App\Http\Repositories\CoponUserRepositories;

class CoponUserRepositoryCaller{

    static public function createRepository(){return (new CoponUserCreateRepository());}
    static public function readRepository(){return (new CoponUserReadRepository());}
    static public function updateRepository(){return (new CoponUserUpdateRepository());}
    static public function deleteRepository(){return (new CoponUserDeleteRepository());}

}