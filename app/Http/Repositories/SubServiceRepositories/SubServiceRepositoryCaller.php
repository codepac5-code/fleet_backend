<?php
namespace App\Http\Repositories\SubServiceRepositories;

use App\Models\SubService;

class SubServiceRepositoryCaller{

static public function createRepository(){return (new SubServiceCreateRepository());}
        static public function readRepository(){return (new SubServiceReadRepository());}
        static public function updateRepository(){return (new SubServiceUpdateRepository());}
        static public function deleteRepository(){return (new SubServiceDeleteRepository());}
        static public function getModel(){return (new SubService());}
}