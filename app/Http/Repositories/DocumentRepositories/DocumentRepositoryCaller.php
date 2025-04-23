<?php
namespace App\Http\Repositories\DocumentRepositories;
use App\Models\{Document};

class DocumentRepositoryCaller{

    static public function createRepository(){return (new DocumentCreateRepository());}
    static public function readRepository(){return (new DocumentReadRepository());}
    static public function updateRepository(){return (new DocumentUpdateRepository());}
    static public function deleteRepository(){return (new DocumentDeleteRepository());}
    static public function get_model() : Document {return (new Document());}


}