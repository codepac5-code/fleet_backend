<?php

namespace App\Http\Core\Repositories\Account;

use App\Models\SafetyContact;
use Illuminate\Support\Collection;

interface SafetyContactRepository
{
    public function listForUser(int $userId): Collection;

    public function create(array $attributes): SafetyContact;

    public function findForUser(int $id, int $userId): ?SafetyContact;

    public function delete(SafetyContact $contact): void;
}
