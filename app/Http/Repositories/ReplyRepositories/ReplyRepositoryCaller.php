<?php
namespace App\Http\Repositories\ReplyRepositories;
use App\Models\{Reply};

class ReplyRepositoryCaller{

    static public function createRepository(){return (new ReplyCreateRepository());}
    static public function readRepository(){return (new ReplyReadRepository());}
    static public function updateRepository(){return (new ReplyUpdateRepository());}
    static public function deleteRepository(){return (new ReplyDeleteRepository());}
    static public function get_model() : Reply {return (new Reply());}


}