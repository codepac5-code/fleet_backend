<?php
namespace App\Http\Repositories\PaymentMethodRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\PaymentMethod;

class PaymentMethodDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new PaymentMethod();
    }
}