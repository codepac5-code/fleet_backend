<?php
namespace App\Http\Repositories\RoleRepositories;
use App\Models\{Role};

class RoleRepositoryCaller{

    static public function createRepository(){return (new RoleCreateRepository());}
    static public function readRepository(){return (new RoleReadRepository());}
    static public function updateRepository(){return (new RoleUpdateRepository());}
    static public function deleteRepository(){return (new RoleDeleteRepository());}
    static public function get_model() : Role {return (new Role());}


}