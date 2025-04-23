<?php
namespace App\Http\Repositories\MtnInvoiceRepositories;
use App\Models\{MtnInvoice};

class MtnInvoiceRepositoryCaller{

    static public function createRepository(){return (new MtnInvoiceCreateRepository());}
    static public function readRepository(){return (new MtnInvoiceReadRepository());}
    static public function updateRepository(){return (new MtnInvoiceUpdateRepository());}
    static public function deleteRepository(){return (new MtnInvoiceDeleteRepository());}
    static public function get_model() : MtnInvoice {return (new MtnInvoice());}


}