<?php

namespace App\Http\Services\Panel\Auth\Logic;

use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * The half-logged-in state between password and TOTP code. The guard is logged
 * out for the duration — only the pending identity (guard, id, shard) survives
 * in the session, so an unfinished challenge grants nothing.
 */
class TwoFactorChallenge
{
    private const KEY = 'panel_two_factor_pending';

    public function __construct(private TwoFactorService $twoFactor)
    {
    }

    public function isNeededFor(string $guard): bool
    {
        $user = Auth::guard($guard)->user();

        return $user !== null && $this->twoFactor->isEnabled($guard, (int) $user->id);
    }

    public function start(string $guard, bool $remember): RedirectResponse
    {
        $user = Auth::guard($guard)->user();
        $pending = [
            'guard' => $guard,
            'id' => (int) $user->id,
            'country' => $this->twoFactor->country($guard),
            'shard' => session('active_shard_id'),
            'remember' => $remember,
        ];

        Auth::guard($guard)->logout();
        session()->regenerate();
        session([self::KEY => $pending]);

        return redirect()->route('panel.two-factor.challenge');
    }

    public function pending(): ?array
    {
        $pending = session(self::KEY);

        return is_array($pending) && isset($pending['guard'], $pending['id']) ? $pending : null;
    }

    public function forget(): void
    {
        session()->forget(self::KEY);
    }

    /** Verifies the code and finishes the login the password step started. */
    public function complete(string $code): ?string
    {
        $pending = $this->pending();

        if ($pending === null) {
            return null;
        }

        $guard = (string) $pending['guard'];

        if (! $this->twoFactor->verify($guard, (int) $pending['id'], $code, $pending['country'] ?? null)) {
            return null;
        }

        $this->restoreShard($pending);
        Auth::guard($guard)->loginUsingId((int) $pending['id'], (bool) ($pending['remember'] ?? false));

        session()->regenerate();
        $this->forget();

        return $guard;
    }

    /**
     * The shard the password step picked has to be put back after the session
     * regenerate, or an office would land on the panel with no active country.
     */
    private function restoreShard(array $pending): void
    {
        $shardId = $pending['shard'] ?? null;

        if ($shardId === null) {
            return;
        }

        session(['active_shard_id' => $shardId]);

        $node = InfrastructureNode::query()->find($shardId);

        if ($node !== null) {
            // Repoint `dynamic` at the shard, not just the context — the 2FA
            // verify step re-queries the office/employee on this connection.
            ShardManager::activate($node);
        }
    }
}
