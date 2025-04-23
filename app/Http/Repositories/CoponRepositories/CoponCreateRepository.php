<?php
namespace App\Http\Repositories\CoponRepositories;

use App\Models\Copon;
use App\Models\Coupon;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;

class CoponCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Coupon();
    }
}
