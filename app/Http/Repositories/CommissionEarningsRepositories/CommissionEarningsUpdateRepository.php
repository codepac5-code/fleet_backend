<?php
namespace App\Http\Repositories\CommissionEarningsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\CommissionEarnings;

class CommissionEarningsUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new CommissionEarnings();
    }

}