<?php
namespace App\Http\Repositories\HelpSuggestionRepositories;
use App\Models\{HelpSuggestion};

class HelpSuggestionRepositoryCaller{

    static public function createRepository(){return (new HelpSuggestionCreateRepository());}
    static public function readRepository(){return (new HelpSuggestionReadRepository());}
    static public function updateRepository(){return (new HelpSuggestionUpdateRepository());}
    static public function deleteRepository(){return (new HelpSuggestionDeleteRepository());}
    static public function get_model() : HelpSuggestion {return (new HelpSuggestion());}


}