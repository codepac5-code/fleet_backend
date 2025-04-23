<?php
namespace App\Http\Repositories\OfficeDocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\OfficeDocument;

class OfficeDocumentUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new OfficeDocument();
    }

}