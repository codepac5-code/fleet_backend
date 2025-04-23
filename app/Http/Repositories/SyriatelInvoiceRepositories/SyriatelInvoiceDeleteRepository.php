<?php
namespace App\Http\Repositories\SyriatelInvoiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\SyriatelInvoice;

class SyriatelInvoiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new SyriatelInvoice();
    }
}