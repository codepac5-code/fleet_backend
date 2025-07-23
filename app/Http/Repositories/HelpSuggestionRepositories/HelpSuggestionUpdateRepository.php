<?php
namespace App\Http\Repositories\HelpSuggestionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\HelpSuggestion;

class HelpSuggestionUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new HelpSuggestion();
    }

}