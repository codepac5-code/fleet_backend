<?php

namespace App\Http\Services\Panel\Shared\Scoping;

use Illuminate\Support\Facades\Auth;

class EntityScope
{
    public const ADMIN    = 'admin';
    public const OFFICE   = 'office';
    public const EMPLOYEE = 'employee';

    private string|null|false $guard = false;
    private bool $userResolved = false;
    private $userInstance = null;

    public function guard(): ?string
    {
        if ($this->guard !== false) {
            return $this->guard;
        }

        foreach ([self::ADMIN, self::OFFICE, self::EMPLOYEE] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $this->guard = $guard;
            }
        }

        return $this->guard = null;
    }

    public function user()
    {
        if ($this->userResolved) {
            return $this->userInstance;
        }

        $this->userResolved = true;
        $guard = $this->guard();

        return $this->userInstance = $guard ? Auth::guard($guard)->user() : null;
    }

    public function isAdmin(): bool
    {
        return $this->guard() === self::ADMIN;
    }

    public function isOffice(): bool
    {
        return $this->guard() === self::OFFICE;
    }

    public function isEmployee(): bool
    {
        return $this->guard() === self::EMPLOYEE;
    }

    public function officeId(): ?int
    {
        $user = $this->user();

        if ($user === null) {
            return null;
        }

        return match ($this->guard()) {
            self::OFFICE   => $user->id,
            self::EMPLOYEE => $user->officeId,
            default        => null,
        };
    }

    public function scopeByOffice($query, string $column = 'officeId')
    {
        $officeId = $this->officeId();

        if ($this->isAdmin() || $officeId === null) {
            return $query;
        }

        return $query->where($column, $officeId);
    }
}
