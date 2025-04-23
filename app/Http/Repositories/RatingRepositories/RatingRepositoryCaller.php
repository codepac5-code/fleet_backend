<?php
namespace App\Http\Repositories\RatingRepositories;
use App\Models\{Rating};

class RatingRepositoryCaller{

    static public function createRepository(){return (new RatingCreateRepository());}
    static public function readRepository(){return (new RatingReadRepository());}
    static public function updateRepository(){return (new RatingUpdateRepository());}
    static public function deleteRepository(){return (new RatingDeleteRepository());}
    static public function get_model() : Rating {return (new Rating());}


}