<?php
namespace App\Http\Repositories\NotficationRepositories;
use App\Models\{Notfication};

class NotficationRepositoryCaller{

    static public function createRepository(){return (new NotficationCreateRepository());}
    static public function readRepository(){return (new NotficationReadRepository());}
    static public function updateRepository(){return (new NotficationUpdateRepository());}
    static public function deleteRepository(){return (new NotficationDeleteRepository());}
    static public function get_model() : Notfication {return (new Notfication());}


}