<?php
namespace App\Http\Repositories\MtnInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\MtnInvoice;

class MtnInvoiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new MtnInvoice();
    }

}