<?php
namespace App\Http\Repositories\SyriatelInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\SyriatelInvoice;

class SyriatelInvoiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new SyriatelInvoice();
    }

}