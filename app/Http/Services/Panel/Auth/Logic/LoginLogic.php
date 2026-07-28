<?php

namespace App\Http\Services\Panel\Auth\Logic;

use App\Http\Core\GeoServices\ShardContext;
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

            ShardContext::set($node);
            session(['active_shard_id' => $node->id]);
        }

        logoutAuthUser();

        $remember = $this->input->isAdmin() ? $this->input->getRemember() : false;

        if (! authenticate($this->input->credentials(), $remember, $guard)) {
            return back()->withErrors(['password' => __('auth.invalid_credentials')])->withInput();
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
