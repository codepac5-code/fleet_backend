<?php
namespace App\Http\Repositories\HelpSuggestionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\HelpSuggestion;

class HelpSuggestionCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new HelpSuggestion();
    }
}