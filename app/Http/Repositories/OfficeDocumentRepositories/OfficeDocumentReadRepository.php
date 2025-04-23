<?php
namespace App\Http\Repositories\OfficeDocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\OfficeDocument;

class OfficeDocumentReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new OfficeDocument();
    }

}