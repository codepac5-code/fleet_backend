<?php

namespace App\Http\Services\User\Auth\Logic;

use App\Http\Core\Classes\Auth\PhoneNumber;
use App\Http\Core\Classes\Notification\SmsSender;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Presenters\UserPresenter;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PhoneChangeService
{
    private const CODE_TTL = 300;

    public function __construct(
        private UserPresenter $presenter,
        private ?SmsSender $sms = null
    ) {
    }

    public function request(int $userId, string $dialCode, string $phone): array
    {
        $normalized = PhoneNumber::normalize($dialCode . $phone);

        if ($normalized === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        $taken = User::query()->where('phoneNumber', $normalized)->where('id', '!=', $userId)->exists();

        if ($taken) {
            throw DomainException::conflict('phone_taken');
        }

        $challengeId = 'chg_' . Str::random(20);
        $code = (string) random_int(1000, 9999);

        Cache::put($this->key($challengeId), ['user_id' => $userId, 'phone' => $normalized, 'code' => $code], now()->addSeconds(self::CODE_TTL));

        $this->deliver($normalized, $code);

        return [
            'challengeId' => $challengeId,
            'expiresIn' => self::CODE_TTL,
            'isNewUser' => false,
        ];
    }

    public function confirm(int $userId, string $challengeId, string $code): array
    {
        $challenge = Cache::get($this->key($challengeId));

        if ($challenge === null) {
            throw DomainException::make('challenge_expired', 410);
        }

        if ((int) $challenge['user_id'] !== $userId) {
            throw DomainException::notFound();
        }

        if (! hash_equals((string) $challenge['code'], trim($code))) {
            throw DomainException::make('invalid_code', 422);
        }

        $user = User::query()->find($userId);

        if ($user === null) {
            throw DomainException::notFound();
        }

        [$dial] = PhoneNumber::split($challenge['phone']);

        $user->phoneNumber = $challenge['phone'];
        $user->dialCode = $dial;
        $user->save();

        Cache::forget($this->key($challengeId));

        return $this->presenter->present($user);
    }

    private function deliver(string $phone, string $code): void
    {
        if ($this->sms !== null) {
            $this->sms->send($phone, 'Your Fleet Ride phone-change code is ' . $code . '. It expires in 5 minutes.');
        }
    }

    private function key(string $challengeId): string
    {
        return 'rider:phone-change:' . $challengeId;
    }
}
