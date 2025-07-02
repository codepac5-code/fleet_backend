<?php
namespace app\Http\Repositories\OfficeRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Office;

class OfficeCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Office();
    }

    public function addOfficeServices( $officeId , $SubServiceIds){
        $office = $this->model::findOrFail($officeId);

        $office->services()->sync($SubServiceIds);
    }
}
