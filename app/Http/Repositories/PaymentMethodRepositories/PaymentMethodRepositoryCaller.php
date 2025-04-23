<?php
namespace App\Http\Repositories\PaymentMethodRepositories;

class PaymentMethodRepositoryCaller{

    static public function createRepository(){return (new PaymentMethodCreateRepository());}
    static public function readRepository(){return (new PaymentMethodReadRepository());}
    static public function updateRepository(){return (new PaymentMethodUpdateRepository());}
    static public function deleteRepository(){return (new PaymentMethodDeleteRepository());}

}