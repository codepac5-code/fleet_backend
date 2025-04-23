<?php
namespace App\Http\Repositories\DocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Document;

class DocumentReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Document();
    }

}