<?php
namespace App\Http\Repositories\HelpSuggestionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\HelpSuggestion;

class HelpSuggestionDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new HelpSuggestion();
    }
}