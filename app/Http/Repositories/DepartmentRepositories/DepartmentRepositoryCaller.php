<?php
namespace App\Http\Repositories\DepartmentRepositories;
use App\Models\{Department};

class DepartmentRepositoryCaller{

    static public function createRepository(){return (new DepartmentCreateRepository());}
    static public function readRepository(){return (new DepartmentReadRepository());}
    static public function updateRepository(){return (new DepartmentUpdateRepository());}
    static public function deleteRepository(){return (new DepartmentDeleteRepository());}
    static public function get_model() : Department {return (new Department());}


}