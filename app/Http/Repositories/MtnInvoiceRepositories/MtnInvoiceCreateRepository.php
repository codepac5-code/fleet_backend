<?php
namespace App\Http\Repositories\MtnInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\MtnInvoice;

class MtnInvoiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new MtnInvoice();
    }
}