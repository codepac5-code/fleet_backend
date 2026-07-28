<?php

namespace App\Http\Services\Panel\Auth\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\InternalInterface\InputServiceInterface;

class LoginInput implements InputServiceInterface
{
    private string $role;
    private ?string $guardName;
    private string $email;
    private string $password;
    private ?string $region;
    private bool $remember;

    public function __construct(array $input)
    {
        $this->role     = $input['role'];
        $this->email    = $input['email'];
        $this->password = $input['password'];
        $this->region   = $input['region'] ?? null;
        $this->remember = (bool) ($input['remember'] ?? false);
        $this->guardName = $this->resolveGuard($this->role);
    }

    private function resolveGuard(string $role): ?string
    {
        return match ($role) {
            'admin'    => Guard::$Admin,
            'manager'  => Guard::$Office,
            'employee' => Guard::$Employee,
            default    => null,
        };
    }

    public function isAdmin(): bool         { return $this->guardName === Guard::$Admin; }
    public function getGuardName(): ?string { return $this->guardName; }
    public function getEmail(): string      { return $this->email; }
    public function getPassword(): string   { return $this->password; }
    public function getRegion(): ?string    { return $this->region; }
    public function getRemember(): bool     { return $this->remember; }

    public function credentials(): array
    {
        return ['email' => $this->email, 'password' => $this->password];
    }

    public function toArray(): array
    {
        return [
            'email'     => $this->email,
            'role'      => $this->role,
            'guardName' => $this->guardName,
            'region'    => $this->region,
            'remember'  => $this->remember,
        ];
    }
}
