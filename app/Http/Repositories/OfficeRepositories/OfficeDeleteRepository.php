<?php
namespace App\Http\Repositories\OfficeRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Office;

class OfficeDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Office();
    }
}
