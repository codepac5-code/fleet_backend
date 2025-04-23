<?php
namespace App\Http\Repositories\CoponServiceRepositories;

class CouponServiceRepositoryCaller{

    static public function createRepository(){return (new CoponServiceCreateRepository());}
    static public function readRepository(){return (new CoponServiceReadRepository());}
    static public function updateRepository(){return (new CoponServiceUpdateRepository());}
    static public function deleteRepository(){return (new CoponServiceDeleteRepository());}

}
