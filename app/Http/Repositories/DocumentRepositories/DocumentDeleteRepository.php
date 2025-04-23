<?php
namespace App\Http\Repositories\DocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Document;

class DocumentDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Document();
    }
}