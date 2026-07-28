<?php

namespace App\Http\Core\Classes\Auth;

use App\Http\Core\Exceptions\DomainException;
use App\Models\User;
use Illuminate\Support\Str;

class RiderProvisioningService
{
    public function findOrCreateByPhone(string $rawPhone, ?string $name = null): array
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            throw DomainException::make('invalid_phone', 422);
        }

        $user = User::query()->where('phoneNumber', $phone)->first();

        if ($user !== null) {
            return [$user, false];
        }

        [$dial] = PhoneNumber::split($phone);
        [$first, $last] = $this->splitName($name);

        $user = new User();
        $user->firstName = $first;
        $user->lastName = $last;
        $user->phoneNumber = $phone;
        $user->dialCode = $dial;
        $user->password = Str::random(40);
        $user->is_registered = false;
        $user->isActive = 1;
        $user->save();

        return [$user, true];
    }

    private function splitName(?string $name): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}
