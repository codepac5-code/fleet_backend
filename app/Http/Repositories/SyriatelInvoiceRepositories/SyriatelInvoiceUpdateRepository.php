<?php
namespace App\Http\Repositories\SyriatelInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\SyriatelInvoice;

class SyriatelInvoiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new SyriatelInvoice();
    }

}