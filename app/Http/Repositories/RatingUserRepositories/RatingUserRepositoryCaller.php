<?php
namespace App\Http\Repositories\RatingUserRepositories;
use App\Models\{RatingUser};

class RatingUserRepositoryCaller{

    static public function createRepository(){return (new RatingUserCreateRepository());}
    static public function readRepository(){return (new RatingUserReadRepository());}
    static public function updateRepository(){return (new RatingUserUpdateRepository());}
    static public function deleteRepository(){return (new RatingUserDeleteRepository());}
    static public function get_model() : RatingUser {return (new RatingUser());}


}