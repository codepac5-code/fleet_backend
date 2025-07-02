<?php
namespace App\Http\Repositories\EmployeeRepositories;
use App\Models\{Employee};

class EmployeeRepositoryCaller{

    static public function createRepository(){return (new EmployeeCreateRepository());}
    static public function readRepository(){return (new EmployeeReadRepository());}
    static public function updateRepository(){return (new EmployeeUpdateRepository());}
    static public function deleteRepository(){return (new EmployeeDeleteRepository());}
    static public function get_model() : Employee {return (new Employee());}


}