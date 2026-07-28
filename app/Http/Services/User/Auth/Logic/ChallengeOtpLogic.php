<?php

namespace App\Http\Services\User\Auth\Logic;

use App\Http\Core\Classes\Auth\PhoneNumber;
use App\Http\Core\Classes\Auth\RiderProvisioningService;
use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Http\Core\Classes\Notification\SmsSender;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Presenters\UserPresenter;
use App\Models\RiderProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChallengeOtpLogic
{
    private const CODE_TTL = 300;
    private const TOKEN_TTL = 3600;

    public function __construct(
        private RiderProvisioningService $riders,
        private TokenIssuer $tokens,
        private RefreshTokenService $refresh,
        private UserPresenter $presenter,
        private ?SmsSender $sms = null
    ) {
    }

    public function request(string $dialCode, string $phone): array
    {
        $normalized = PhoneNumber::normalize($dialCode . $phone);

        if ($normalized === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        $challengeId = 'chg_' . Str::random(20);
        $code = (string) random_int(1000, 9999);

        Cache::put($this->key($challengeId), ['phone' => $normalized, 'code' => $code, 'verified' => false], now()->addSeconds(self::CODE_TTL));

        $this->deliver($normalized, $code);

        // Dev convenience, mirroring DriverAuthService::requestOtp: there is no
        // SMS gateway locally and WhatsApp delivery is unreliable in dev, so the
        // code is surfaced to the log and the response. Gated to local/debug —
        // it must never leak in production, where the field is absent entirely.
        $devReveal = app()->environment('local') || config('app.debug');
        if ($devReveal) {
            Log::info("[RiderOTP] {$normalized} => {$code}");
        }

        return array_filter([
            'challengeId' => $challengeId,
            'expiresIn' => self::CODE_TTL,
            'isNewUser' => User::query()->where('phoneNumber', $normalized)->doesntExist(),
            'devCode' => $devReveal ? $code : null,
        ], fn ($v) => $v !== null);
    }

    public function verify(string $challengeId, string $code): array
    {
        $challenge = $this->assertCode($challengeId, $code);

        Cache::put($this->key($challengeId), array_merge($challenge, ['verified' => true]), now()->addSeconds(self::CODE_TTL));

        [$user] = $this->riders->findOrCreateByPhone($challenge['phone']);

        return $this->session($user);
    }

    public function register(string $challengeId, string $firstName, string $lastName, ?string $email, ?string $country): array
    {
        $challenge = Cache::get($this->key($challengeId));

        if ($challenge === null || empty($challenge['verified'])) {
            throw DomainException::make('challenge_not_verified', 422);
        }

        [$user] = $this->riders->findOrCreateByPhone($challenge['phone'], trim($firstName . ' ' . $lastName));

        $user->firstName = trim($firstName);
        $user->lastName = trim($lastName);

        if ($country !== null) {
            $user->current_country = $country;
        }

        $user->is_registered = true;
        $user->save();

        if ($email !== null && $email !== '') {
            $this->storeEmail((int) $user->id, $email);
        }

        Cache::forget($this->key($challengeId));

        return $this->session($user);
    }

    public function refresh(string $refreshToken): array
    {
        $userId = $this->refresh->consume($refreshToken);

        if ($userId === null) {
            throw DomainException::make('invalid_refresh_token', 401);
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            throw DomainException::make('invalid_refresh_token', 401);
        }

        return [
            'accessToken' => $this->tokens->issue($user, 'fleet-ride'),
            'refreshToken' => $this->refresh->issue((int) $user->id),
            'tokenType' => 'Bearer',
            'expiresIn' => self::TOKEN_TTL,
        ];
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

    private function assertCode(string $challengeId, string $code): array
    {
        $challenge = Cache::get($this->key($challengeId));

        if ($challenge === null) {
            throw DomainException::make('challenge_expired', 410);
        }

        if (! hash_equals((string) $challenge['code'], trim($code))) {
            throw DomainException::make('invalid_code', 422);
        }

        return $challenge;
    }

    private function storeEmail(int $userId, string $email): void
    {
        $profile = RiderProfile::query()->firstOrNew(['user_id' => $userId]);
        $profile->email = $email;
        $profile->save();
    }

    private function deliver(string $phone, string $code): void
    {
        if ($this->sms !== null) {
            $this->sms->send($phone, 'Your Fleet Ride verification code is ' . $code . '. It expires in 5 minutes.');
        }
    }

    private function key(string $challengeId): string
    {
        return 'rider:challenge:' . $challengeId;
    }
}
