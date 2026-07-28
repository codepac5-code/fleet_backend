<?php

namespace App\Http\Core\Classes\Auth;

use App\Http\Core\Classes\Notification\NotificationService;
use App\Http\Core\Classes\Settings\AppSettings;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Exceptions\DomainException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class OtpAuthService
{
    private const EXPIRES = 120;
    private const RESEND = 60;
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private TokenIssuer $tokens,
        private SocialVerifier $social,
        private NotificationService $notifications
    ) {
    }

    public function requestOtp(string $rawPhone): array
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        if (Cache::has($this->sentKey($phone))) {
            throw DomainException::make('otp_throttled', 429);
        }

        $code = (string) random_int(1000, 9999);
        $ttl = AppSettings::int('otp_ttl_seconds', self::EXPIRES);

        Cache::put($this->codeKey($phone), $code, now()->addSeconds($ttl));
        Cache::put($this->sentKey($phone), true, now()->addSeconds(self::RESEND));
        Cache::forget($this->attemptsKey($phone));

        $this->deliver($phone, $code);

        return [
            'otp_sent' => true,
            'expires_in' => $ttl,
            'resend_in' => self::RESEND,
        ];
    }

    public function verify(string $rawPhone, string $code, ?array $device): array
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        $stored = Cache::get($this->codeKey($phone));

        if ($stored === null) {
            throw DomainException::make('code_expired', 410);
        }

        if ((int) Cache::get($this->attemptsKey($phone), 0) >= self::MAX_ATTEMPTS) {
            Cache::forget($this->codeKey($phone));
            throw DomainException::make('code_expired', 410);
        }

        if (! hash_equals((string) $stored, trim($code))) {
            Cache::increment($this->attemptsKey($phone));
            throw DomainException::make('invalid_code', 422);
        }

        Cache::forget($this->codeKey($phone));
        Cache::forget($this->sentKey($phone));
        Cache::forget($this->attemptsKey($phone));

        [$user, $isNew] = $this->findOrCreateUser($phone);

        $this->registerDevice($user, $device);

        return [
            'access_token' => $this->tokens->issue($user, 'fleet-ride'),
            'token_type' => 'Bearer',
            'expires_at' => null,
            'is_new_user' => $isNew,
            'user' => $this->presentUser($user),
        ];
    }

    public function social(string $provider, string $idToken, ?array $device): array
    {
        if (! in_array($provider, ['apple', 'google'], true)) {
            throw DomainException::make('invalid_provider', 422);
        }

        $profile = $this->social->verify($provider, $idToken);

        $phone = PhoneNumber::normalize((string) ($profile['phone'] ?? ''));

        if ($phone === null) {
            throw DomainException::make('social_phone_required', 422);
        }

        [$user, $isNew] = $this->findOrCreateUser($phone, $profile);

        $this->registerDevice($user, $device);

        return [
            'access_token' => $this->tokens->issue($user, 'fleet-ride'),
            'token_type' => 'Bearer',
            'expires_at' => null,
            'is_new_user' => $isNew,
            'user' => $this->presentUser($user),
        ];
    }

    public function logout(User $user): void
    {
        $this->tokens->revokeCurrent($user);
    }

    public function presentUser(User $user): array
    {
        $name = trim(((string) $user->firstName) . ' ' . ((string) $user->lastName));

        return [
            'id' => (int) $user->id,
            'name' => $name,
            'phone' => PhoneNumber::mask((string) $user->phoneNumber),
            'phone_verified' => true,
            'locale' => app()->getLocale() ?: 'en',
            'avatar_url' => $user->photo ? asset('storage/' . $user->photo) : null,
        ];
    }

    private function findOrCreateUser(string $phone, array $profile = []): array
    {
        $user = User::query()->where('phoneNumber', $phone)->first();

        if ($user !== null) {
            return [$user, false];
        }

        [$dial, ] = PhoneNumber::split($phone);

        $user = new User();
        $user->firstName = (string) ($profile['first_name'] ?? '');
        $user->lastName = (string) ($profile['last_name'] ?? '');
        $user->phoneNumber = $phone;
        $user->dialCode = $dial;
        $user->password = Str::random(40);
        $user->is_registered = false;
        $user->isActive = 1;
        $user->save();

        return [$user, true];
    }

    private function registerDevice(User $user, ?array $device): void
    {
        $token = is_array($device) ? (string) ($device['token'] ?? '') : '';

        if ($token === '') {
            return;
        }

        try {
            $this->notifications->registerDevice(
                OwnerType::USER,
                (int) $user->id,
                $token,
                $device['platform'] ?? null
            );
        } catch (Throwable $e) {
        }
    }

    private function deliver(string $phone, string $code): void
    {
    }

    private function codeKey(string $phone): string
    {
        return 'otp:code:' . $phone;
    }

    private function sentKey(string $phone): string
    {
        return 'otp:sent:' . $phone;
    }

    private function attemptsKey(string $phone): string
    {
        return 'otp:attempts:' . $phone;
    }
}
