<?php
namespace App\Http\Repositories\OfficeRepositories;


    class OfficeRepositoryCaller{

        static public function createRepository(){return (new OfficeCreateRepository());}
        static public function readRepository(){return (new OfficeReadRepository());}
        static public function updateRepository(){return (new OfficeUpdateRepository());}
        static public function deleteRepository(){return (new OfficeDeleteRepository());}

    }