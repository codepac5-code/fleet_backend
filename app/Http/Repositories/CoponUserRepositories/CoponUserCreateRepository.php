<?php
namespace App\Http\Repositories\CoponUserRepositories;
use App\Models\CouponUser;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;

class CoponUserCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new CouponUser();
    }
}
