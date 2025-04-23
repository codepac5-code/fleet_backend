<?php
namespace App\Http\Repositories\CoponUserRepositories;


use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\CouponUser;

class CoponUserUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new CouponUser();
    }

}
