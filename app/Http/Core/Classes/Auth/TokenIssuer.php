<?php

namespace App\Http\Core\Classes\Auth;

use App\Models\User;

interface TokenIssuer
{
    public function issue(User $user, string $name): string;

    public function revokeCurrent(User $user): void;
}
