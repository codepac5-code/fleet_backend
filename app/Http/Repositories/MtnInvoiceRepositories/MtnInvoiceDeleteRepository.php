<?php
namespace App\Http\Repositories\MtnInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\MtnInvoice;

class MtnInvoiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new MtnInvoice();
    }
}