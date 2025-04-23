<?php
namespace App\Http\Repositories\DocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Document;

class DocumentCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Document();
    }
}