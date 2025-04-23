<?php
namespace App\Http\Repositories\AddressRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Address;

class AddressReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Address();
    }

}
