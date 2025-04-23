<?php
namespace App\Http\Repositories\OfficeDocumentRepositories;
use App\Models\{OfficeDocument};

class OfficeDocumentRepositoryCaller{

    static public function createRepository(){return (new OfficeDocumentCreateRepository());}
    static public function readRepository(){return (new OfficeDocumentReadRepository());}
    static public function updateRepository(){return (new OfficeDocumentUpdateRepository());}
    static public function deleteRepository(){return (new OfficeDocumentDeleteRepository());}
    static public function get_model() : OfficeDocument {return (new OfficeDocument());}


}