<?php
namespace App\Http\Repositories\PaymentMethodRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\PaymentMethod;

class PaymentMethodCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new PaymentMethod();
    }
}