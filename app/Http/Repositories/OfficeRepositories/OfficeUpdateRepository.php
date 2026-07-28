<?php
namespace app\Http\Repositories\OfficeRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Office;

class OfficeUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Office();
    }

    public function updateOfficeServices( $officeId, array $serviceIds): void
    {
            $this->model->find($officeId)->services()->sync($serviceIds);
            return;
    }
}
