<?php

namespace App\Providers;

use App\Http\Core\Const\Options\Roles;
use App\Http\Services\Panel\Shared\ViewComposers\BillingStatusComposer;
use App\Http\Services\Panel\Shared\ViewComposers\CountrySwitcherComposer;
use App\Http\Services\Panel\Shared\ViewComposers\NotificationComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PanelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole(Roles::Super_Admin->value)) {
                return true;
            }

            return null;
        });

        View::composer('panel.partials.topbar', CountrySwitcherComposer::class);
        View::composer('panel.partials.topbar', NotificationComposer::class);
        View::composer('panel.partials.topbar', BillingStatusComposer::class);
    }
}
