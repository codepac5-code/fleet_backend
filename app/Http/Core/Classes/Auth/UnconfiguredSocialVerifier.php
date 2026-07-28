<?php

namespace App\Http\Core\Classes\Auth;

use App\Http\Core\Exceptions\DomainException;

class UnconfiguredSocialVerifier implements SocialVerifier
{
    public function verify(string $provider, string $idToken): array
    {
        throw DomainException::make('social_unavailable', 422);
    }
}
