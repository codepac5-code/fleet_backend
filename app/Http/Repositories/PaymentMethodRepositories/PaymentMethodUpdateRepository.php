<?php
namespace App\Http\Repositories\PaymentMethodRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\PaymentMethod;

class PaymentMethodUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new PaymentMethod();
    }

}