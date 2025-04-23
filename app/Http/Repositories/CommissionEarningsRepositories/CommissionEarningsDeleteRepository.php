<?php
namespace App\Http\Repositories\CommissionEarningsRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\CommissionEarnings;

class CommissionEarningsDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new CommissionEarnings();
    }
}