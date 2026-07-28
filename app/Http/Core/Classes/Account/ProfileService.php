<?php

namespace App\Http\Core\Classes\Account;

use App\Http\Core\Classes\Auth\PhoneNumber;
use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Http\Core\Repositories\Account\RiderProfileRepository;
use App\Models\RiderProfile;
use App\Models\User;

class ProfileService
{
    public function __construct(
        private TokenIssuer $tokens,
        private RiderProfileRepository $profiles
    ) {
    }

    public function get(User $user): array
    {
        $profile = $this->profile($user);
        $name = trim(((string) $user->firstName) . ' ' . ((string) $user->lastName));

        return [
            'id' => (int) $user->id,
            'name' => $name,
            'phone_masked' => PhoneNumber::mask((string) $user->phoneNumber),
            'phone_verified' => true,
            'email' => $profile->email,
            'locale' => $profile->locale ?: (app()->getLocale() ?: 'en'),
            'avatar_url' => $user->photo ? asset('storage/' . $user->photo) : null,
        ];
    }

    public function update(User $user, array $attrs): array
    {
        if (array_key_exists('firstName', $attrs)) {
            $user->firstName = trim((string) $attrs['firstName']);
        }

        if (array_key_exists('lastName', $attrs)) {
            $user->lastName = trim((string) $attrs['lastName']);
        }

        $user->save();

        $profile = $this->profile($user);

        if (array_key_exists('email', $attrs)) {
            $profile->email = $attrs['email'];
        }

        if (array_key_exists('locale', $attrs)) {
            $profile->locale = $attrs['locale'];
        }

        $this->profiles->save($profile);

        return $this->get($user);
    }

    public function setAvatar(User $user, string $path): array
    {
        $user->photo = $path;
        $user->save();

        return $this->get($user);
    }

    public function deleteAccount(User $user): void
    {
        $this->tokens->revokeCurrent($user);
        $user->delete();
    }

    private function profile(User $user): RiderProfile
    {
        return $this->profiles->forUser((int) $user->id);
    }
}
