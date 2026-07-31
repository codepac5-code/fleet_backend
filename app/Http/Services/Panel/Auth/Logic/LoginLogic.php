<?php

namespace App\Http\Services\Panel\Auth\Logic;

use App\Http\Core\GeoServices\ShardManager;
use App\Http\Core\InternalInterface\Service;
use App\Models\InfrastructureNode;
use Illuminate\Http\RedirectResponse;

class LoginLogic implements Service
{
    public function __construct(private LoginInput $input) {}

    public function execute(): RedirectResponse
    {
        $guard = $this->input->getGuardName();

        if (! $guard) {
            return back()->withErrors(['role' => __('auth.invalid_account_type')])->withInput();
        }

        if (! $this->input->isAdmin()) {
            $node = InfrastructureNode::where('type', 'country')
                ->where('id', $this->input->getRegion())
                ->first();

            if (! $node) {
                return back()->withErrors(['region' => __('auth.unsupported_region')])->withInput();
            }

            // Activate the shard (not just the context): office/employee tables
            // live on it, so the `dynamic` connection must be repointed at this
            // country's DB before `authenticate()` queries them. `ShardContext::set`
            // alone left `dynamic` host=null → "Database hosts array is empty".
            // NOTE: `active_shard_id` is stored AFTER logoutAuthUser() below —
            // that call invalidates the session, which would otherwise WIPE the
            // marker, leaving the panel to fall back to the first country and
            // resolve office #1 on the WRONG shard (every shard has an id=1).
            ShardManager::activate($node);
        }

        logoutAuthUser();

        if (! $this->input->isAdmin() && isset($node)) {
            session(['active_shard_id' => $node->id]);
        }

        $remember = $this->input->isAdmin() ? $this->input->getRemember() : false;

        if (! authenticate($this->input->credentials(), $remember, $guard)) {
            return back()->withErrors(['password' => __('auth.invalid_credentials')])->withInput();
        }

        // A confirmed second factor turns the password step into half a login:
        // the guard is logged straight back out and only the pending identity is
        // carried to the challenge screen.
        $challenge = app(TwoFactorChallenge::class);

        if ($challenge->isNeededFor($guard)) {
            return $challenge->start($guard, $remember);
        }

        session()->regenerate();

        // Staff login history — best-effort (never blocks the login). Visible in
        // the audit-log viewer, filter action = staff.login.
        $user = auth()->guard($guard)->user();
        if ($user !== null) {
            app(\App\Http\Core\Classes\Audit\AuditLogService::class)->record(
                'staff.login',
                $guard,
                (int) $user->id,
                null,
                null,
                [],
                request()->ip()
            );
        }

        return redirect()->route("panel.{$guard}.home");
    }
}
