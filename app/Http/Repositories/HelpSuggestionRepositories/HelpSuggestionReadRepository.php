<?php
namespace App\Http\Repositories\HelpSuggestionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\HelpSuggestion;

class HelpSuggestionReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new HelpSuggestion();
    }

}