<?php

namespace App\Http\Services\User\Auth\Logic;

use App\Http\Core\Classes\Auth\RiderProvisioningService;
use App\Http\Core\Classes\Auth\SocialVerifier;
use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Presenters\UserPresenter;
use App\Models\RiderProfile;
use App\Models\User;

class SocialAuthLogic
{
    private const TOKEN_TTL = 3600;

    public function __construct(
        private SocialVerifier $verifier,
        private RiderProvisioningService $riders,
        private TokenIssuer $tokens,
        private RefreshTokenService $refresh,
        private UserPresenter $presenter
    ) {
    }

    public function login(string $provider, string $idToken): array
    {
        if (! in_array($provider, ['apple', 'google'], true)) {
            throw DomainException::make('invalid_provider', 422);
        }

        $profile = $this->verifier->verify($provider, $idToken);

        $phone = (string) ($profile['phone'] ?? '');

        if ($phone === '') {
            throw DomainException::make('social_phone_required', 422);
        }

        $name = trim(((string) ($profile['first_name'] ?? '')) . ' ' . ((string) ($profile['last_name'] ?? '')));

        [$user, $isNew] = $this->riders->findOrCreateByPhone($phone, $name !== '' ? $name : null);

        if (! empty($profile['email'])) {
            $this->storeEmail((int) $user->id, (string) $profile['email']);
        }

        return array_merge($this->session($user), ['isNewUser' => $isNew]);
    }

    private function session(User $user): array
    {
        return [
            'accessToken' => $this->tokens->issue($user, 'fleet-ride'),
            'refreshToken' => $this->refresh->issue((int) $user->id),
            'tokenType' => 'Bearer',
            'expiresIn' => self::TOKEN_TTL,
            'user' => $this->presenter->present($user),
        ];
    }

    private function storeEmail(int $userId, string $email): void
    {
        $profile = RiderProfile::query()->firstOrNew(['user_id' => $userId]);
        $profile->email = $email;
        $profile->save();
    }
}
