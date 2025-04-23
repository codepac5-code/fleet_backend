<?php
namespace App\Http\Repositories\CommissionEarningsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\CommissionEarnings;

class CommissionEarningsReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new CommissionEarnings();
    }

}