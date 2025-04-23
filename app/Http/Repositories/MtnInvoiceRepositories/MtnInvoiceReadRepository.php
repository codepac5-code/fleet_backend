<?php
namespace App\Http\Repositories\MtnInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\MtnInvoice;

class MtnInvoiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new MtnInvoice();
    }

}