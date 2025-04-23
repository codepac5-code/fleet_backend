<?php
namespace App\Http\Repositories\SyriatelInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\SyriatelInvoice;

class SyriatelInvoiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new SyriatelInvoice();
    }
}