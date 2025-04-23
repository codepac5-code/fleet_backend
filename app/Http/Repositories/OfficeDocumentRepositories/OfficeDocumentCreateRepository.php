<?php
namespace App\Http\Repositories\OfficeDocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\OfficeDocument;

class OfficeDocumentCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new OfficeDocument();
    }
}