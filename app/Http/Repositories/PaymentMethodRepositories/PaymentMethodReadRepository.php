<?php
namespace App\Http\Repositories\PaymentMethodRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\PaymentMethod;

class PaymentMethodReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new PaymentMethod();
    }

}