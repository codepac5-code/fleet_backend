<?php
namespace App\Http\Repositories\DocumentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Document;

class DocumentUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Document();
    }

}