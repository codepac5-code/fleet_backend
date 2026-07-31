<?php

namespace App\Http\Services\Panel\Security\Logic;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\SiteSetting;
use Throwable;

/**
 * Who the acting staff member is for 2FA purposes, and whether their guard is
 * required to enrol. The requirement is a platform setting (default OFF) so a
 * misconfiguration can never lock an operator out of a fresh install.
 */
class StaffIdentity
{
    public const REQUIRE_KEY = 'security_2fa_required';

    public function id(EntityScope $scope): int
    {
        $user = $scope->user();

        return $user !== null ? (int) $user->id : 0;
    }

    public function label(EntityScope $scope): string
    {
        $user = $scope->user();

        if ($user === null) {
            return (string) $scope->guard();
        }

        foreach (['email', 'userName', 'phoneNumber', 'officeName', 'name'] as $field) {
            $value = $user->{$field} ?? null;

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $scope->guard() . '#' . $user->id;
    }

    /** Guards that must enrol: '' (none), 'admin', or 'all'. */
    public function requirement(): string
    {
        try {
            $value = SiteSetting::val(self::REQUIRE_KEY);

            return in_array($value, ['admin', 'all'], true) ? $value : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    public function isRequired(string $guard): bool
    {
        $requirement = $this->requirement();

        return $requirement === 'all' || ($requirement === 'admin' && $guard === 'admin');
    }
}
