<?php

namespace App\Http\Core\Classes\Auth;

interface SocialVerifier
{
    public function verify(string $provider, string $idToken): array;
}
