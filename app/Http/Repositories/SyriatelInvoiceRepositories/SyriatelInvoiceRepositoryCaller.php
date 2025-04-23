<?php
namespace App\Http\Repositories\SyriatelInvoiceRepositories;
use App\Models\{SyriatelInvoice};

class SyriatelInvoiceRepositoryCaller{

    static public function createRepository(){return (new SyriatelInvoiceCreateRepository());}
    static public function readRepository(){return (new SyriatelInvoiceReadRepository());}
    static public function updateRepository(){return (new SyriatelInvoiceUpdateRepository());}
    static public function deleteRepository(){return (new SyriatelInvoiceDeleteRepository());}
    static public function get_model() : SyriatelInvoice {return (new SyriatelInvoice());}


}