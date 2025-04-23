<?php
namespace App\Http\Repositories\CoponRepositories;

class CouponRepositoryCaller{

    static public function createRepository(){return (new CoponCreateRepository());}
    static public function readRepository(){return (new CoponReadRepository());}
    static public function updateRepository(){return (new CoponUpdateRepository());}
    static public function deleteRepository(){return (new CoponDeleteRepository());}

}
