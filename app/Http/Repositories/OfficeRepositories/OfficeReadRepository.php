<?php
namespace app\Http\Repositories\OfficeRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Office;

class OfficeReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Office();
    }

}
