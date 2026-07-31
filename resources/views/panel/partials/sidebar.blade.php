@php
    use App\Http\Services\Panel\Shared\Authorization\PanelPermission as Perm;

    $r = fn (string $name) => 'panel.' . $entity . '.' . $name;
    $has = fn (string $name) => \Illuminate\Support\Facades\Route::has('panel.' . $entity . '.' . $name);
@endphp

<aside class="panel-sidebar">
    @php
        $panelUser = auth()->guard($entity)->user();
        $accountMap = [
            'admin'    => ['icon' => 'bi-shield-lock',   'label' => textByLanguage('مدير النظام', 'Super Admin')],
            'office'   => ['icon' => 'bi-building',       'label' => textByLanguage('مكتب', 'Office')],
            'employee' => ['icon' => 'bi-person-badge',   'label' => textByLanguage('موظف', 'Employee')],
        ];
        $account = $accountMap[$entity] ?? $accountMap['admin'];
        $panelUserName = $panelUser->displayName ?? $panelUser->officeName ?? trim(($panelUser->firstName ?? '') . ' ' . ($panelUser->lastName ?? ''));
    @endphp

    <div class="panel-brand">
        <div class="panel-brand__main">
            <span class="panel-brand__spin" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" opacity=".25"/>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-dasharray="20 60"/>
                </svg>
            </span>
            {{-- The platform is fleetOS; an office signs into fleet.Office. --}}
            <x-panel.wordmark :product="$entity === 'admin' ? 'os' : 'office'" tone="dark" :tagline="$entity === 'admin'" />
        </div>

        <div class="panel-brand__account">
            <span class="panel-brand__role"><i class="bi {{ $account['icon'] }}"></i></span>
            <div class="panel-brand__who">
                <strong>{{ $account['label'] }}</strong>
                @if($panelUserName)<span>{{ $panelUserName }}</span>@endif
            </div>
        </div>
    </div>

    <ul class="panel-nav">

        <li class="panel-nav__section">{{ __('messages.main') }}</li>

        <li>
            <a class="panel-nav__link {{ request()->routeIs($r('home')) ? 'is-active' : '' }}" href="{{ route($r('home')) }}">
                <i class="bi bi-grid-1x2"></i>
                <span>{{ __('messages.dashboard') }}</span>
            </a>
        </li>

        @can(Perm::ORDER_HISTORY)
            {{-- Rides created by the apps are THE orders; the legacy `bookings`
                 table below is the old dashboard's archive and stopped filling
                 up, so it is no longer the headline entry. --}}
            @if($has('rides.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('rides.index')) || request()->routeIs($r('rides.show')) ? 'is-active' : '' }}" href="{{ route($r('rides.index')) }}">
                    <i class="bi bi-card-checklist"></i><span>{{ __('messages.orders') }}</span></a></li>
            @endif
            @if($has('booking.scheduled'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('booking.scheduled')) ? 'is-active' : '' }}" href="{{ route($r('booking.scheduled')) }}">
                    <i class="bi bi-calendar-event"></i><span>{{ textByLanguage('الرحلات المجدولة', 'Scheduled trips') }}</span></a></li>
            @endif
            @if($has('booking.live'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('booking.live')) ? 'is-active' : '' }}" href="{{ route($r('booking.live')) }}">
                    <i class="bi bi-broadcast-pin"></i><span>{{ textByLanguage('الرحلات الفورية', 'Live trips') }}</span>
                    <span class="panel-nav__live"></span></a></li>
            @endif
            @if($has('office-bookings.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('office-bookings.index')) ? 'is-active' : '' }}" href="{{ route($r('office-bookings.index')) }}">
                    <i class="bi bi-clipboard-plus"></i><span>{{ textByLanguage('حجز مكتبي', 'Manual booking') }}</span></a></li>
            @endif
            @if($has('booking.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('booking.index')) || request()->routeIs($r('booking.show')) ? 'is-active' : '' }}" href="{{ route($r('booking.index')) }}">
                    <i class="bi bi-archive"></i><span>{{ textByLanguage('أرشيف الطلبات القديم', 'Legacy order archive') }}</span></a></li>
            @endif
        @endcan

        <li class="panel-nav__section">{{ __('messages.subscribers') }}</li>

        @can(Perm::VIEW_OFFICE_LIST)
            @if($has('office.index'))
                <li><a class="panel-nav__link" href="{{ route($r('office.index')) }}">
                    <i class="bi bi-building"></i><span>{{ __('messages.offices') }}</span></a></li>
            @endif
        @endcan

        @can(Perm::VIEW_USER_LIST)
            @if($has('user.index'))
                <li><a class="panel-nav__link" href="{{ route($r('user.index')) }}">
                    <i class="bi bi-people"></i><span>{{ __('messages.users') }}</span></a></li>
            @endif
        @endcan

        @can(Perm::VIEW_DRIVER_LIST)
            @if($has('driver.index'))
                <li><a class="panel-nav__link" href="{{ route($r('driver.index')) }}">
                    <i class="bi bi-taxi-front"></i><span>{{ __('messages.drivers') }}</span></a></li>
            @endif
        @endcan

        @can(Perm::VIEW_VEHICLE_LIST)
            @if($has('vehicle.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('vehicle.index')) || request()->routeIs($r('vehicle.create')) || request()->routeIs($r('vehicle.edit')) ? 'is-active' : '' }}" href="{{ route($r('vehicle.index')) }}">
                    <i class="bi bi-car-front"></i><span>{{ textByLanguage('المركبات', 'Vehicles') }}</span></a></li>
            @endif
        @endcan

        @can(Perm::VIEW_EMPLOYEE_LIST)
            @if($has('employee.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('employee.index')) || request()->routeIs($r('employee.create')) || request()->routeIs($r('employee.edit')) ? 'is-active' : '' }}" href="{{ route($r('employee.index')) }}">
                    <i class="bi bi-person-badge"></i><span>{{ __('messages.employees') }}</span></a></li>
            @endif
        @endcan

        @can(Perm::VIEW_COMMISSION)
            @if($has('wallet.transactions'))
                <li class="panel-nav__section">{{ textByLanguage('المالية', 'Finance') }}</li>
                <li><a class="panel-nav__link {{ request()->routeIs($r('wallet.transactions')) ? 'is-active' : '' }}" href="{{ route($r('wallet.transactions')) }}">
                    <i class="bi bi-wallet2"></i><span>{{ textByLanguage('المعاملات المالية', 'Transactions') }}</span></a></li>
                @if($has('payouts.index'))
                    <li><a class="panel-nav__link {{ request()->routeIs($r('payouts.index')) ? 'is-active' : '' }}" href="{{ route($r('payouts.index')) }}">
                        <i class="bi bi-cash-coin"></i><span>{{ textByLanguage('المستحقّات', 'Payouts') }}</span></a></li>
                @endif
                @if($has('reports.fleet') || $has('reports.summary'))
                    @php $reportsRoute = $has('reports.fleet') ? $r('reports.fleet') : $r('reports.summary'); @endphp
                    <li><a class="panel-nav__link {{ request()->routeIs($reportsRoute) ? 'is-active' : '' }}" href="{{ route($reportsRoute) }}">
                        <i class="bi bi-graph-up"></i><span>{{ textByLanguage('التقارير', 'Reports') }}</span></a></li>
                @endif
                @if($has('corporate.invoices'))
                    <li><a class="panel-nav__link {{ request()->routeIs($r('corporate.invoices')) ? 'is-active' : '' }}" href="{{ route($r('corporate.invoices')) }}">
                        <i class="bi bi-briefcase"></i><span>{{ textByLanguage('حسابات الأعمال', 'Business accounts') }}</span></a></li>
                @endif
            @endif
        @endcan

        {{-- An office's own subscription is its ACCOUNT, not a commission
             report. It used to sit inside the Finance block, behind
             `view commission` and behind the Transactions route — so an office
             that holds neither (which is every office set up from the
             permission matrix) had no way at all to reach the one page that
             asks it to pay before its trial runs out. --}}
        @if($has('subscription.show') || $has('commission.index'))
            <li class="panel-nav__section">{{ textByLanguage('حساب المكتب', 'Office account') }}</li>
            @if($has('subscription.show'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('subscription.show')) ? 'is-active' : '' }}" href="{{ route($r('subscription.show')) }}">
                    <i class="bi bi-award"></i><span>{{ textByLanguage('الاشتراك', 'Subscription') }}</span></a></li>
            @endif
            @if($has('commission.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('commission.index')) ? 'is-active' : '' }}" href="{{ route($r('commission.index')) }}">
                    <i class="bi bi-percent"></i><span>{{ textByLanguage('العمولات', 'Commissions') }}</span></a></li>
            @endif
        @endif

        @can(Perm::VIEW_SUB_SERVICE_LIST)
            @if($has('services.mine'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('services.mine')) ? 'is-active' : '' }}" href="{{ route($r('services.mine')) }}">
                    <i class="bi bi-grid-1x2"></i><span>{{ textByLanguage('خدماتي', 'My services') }}</span></a></li>
            @endif
            @if($has('pricing.corridors.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('pricing.corridors.index')) ? 'is-active' : '' }}" href="{{ route($r('pricing.corridors.index')) }}">
                    <i class="bi bi-signpost-split"></i><span>{{ textByLanguage('أسعار الخطوط', 'Fixed corridors') }}</span></a></li>
            @endif
        @endcan

        @if($has('rider-support.index') || $has('ride-ratings.index') || $has('chat.index'))
            <li class="panel-nav__section">{{ textByLanguage('الدعم والجودة', 'Support & Quality') }}</li>
            @if($has('rider-support.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('rider-support.index')) || request()->routeIs($r('rider-support.show')) ? 'is-active' : '' }}" href="{{ route($r('rider-support.index')) }}">
                    <i class="bi bi-life-preserver"></i><span>{{ textByLanguage('دعم الراكب', 'Rider support') }}</span></a></li>
            @endif
            @if($has('chat.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('chat.index')) || request()->routeIs($r('chat.show')) ? 'is-active' : '' }}" href="{{ route($r('chat.index')) }}">
                    <i class="bi bi-chat-dots"></i><span>{{ textByLanguage('محادثات الركّاب', 'Rider chats') }}</span></a></li>
            @endif
            @if($has('ride-ratings.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('ride-ratings.index')) ? 'is-active' : '' }}" href="{{ route($r('ride-ratings.index')) }}">
                    <i class="bi bi-star-half"></i><span>{{ textByLanguage('التقييمات', 'Ratings') }}</span></a></li>
            @endif
            @if($has('driver-safety.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('driver-safety.index')) ? 'is-active' : '' }}" href="{{ route($r('driver-safety.index')) }}">
                    <i class="bi bi-shield-exclamation"></i><span>{{ textByLanguage('سلامة السائقين', 'Driver safety') }}</span></a></li>
            @endif
            @if($has('driver-presence.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('driver-presence.index')) ? 'is-active' : '' }}" href="{{ route($r('driver-presence.index')) }}">
                    <i class="bi bi-broadcast"></i><span>{{ textByLanguage('حضور السائقين', 'Driver presence') }}</span></a></li>
            @endif
            @if($has('announcements.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('announcements.index')) ? 'is-active' : '' }}" href="{{ route($r('announcements.index')) }}">
                    <i class="bi bi-megaphone"></i><span>{{ textByLanguage('إرسال إشعار', 'Announcements') }}</span></a></li>
            @endif
            @if($has('driver-applications.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('driver-applications.index')) ? 'is-active' : '' }}" href="{{ route($r('driver-applications.index')) }}">
                    <i class="bi bi-person-plus"></i><span>{{ textByLanguage('طلبات السائقين', 'Driver applications') }}</span></a></li>
            @endif
            @if($has('complaints.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('complaints.index')) ? 'is-active' : '' }}" href="{{ route($r('complaints.index')) }}">
                    <i class="bi bi-flag"></i><span>{{ textByLanguage('الشكاوى', 'Complaints') }}</span></a></li>
            @endif
            @if($has('lost-items.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('lost-items.index')) ? 'is-active' : '' }}" href="{{ route($r('lost-items.index')) }}">
                    <i class="bi bi-bag"></i><span>{{ textByLanguage('المفقودات', 'Lost & found') }}</span></a></li>
            @endif
            @if($has('family-members.index'))
                <li><a class="panel-nav__link {{ request()->routeIs($r('family-members.index')) ? 'is-active' : '' }}" href="{{ route($r('family-members.index')) }}">
                    <i class="bi bi-people"></i><span>{{ textByLanguage('حسابات العائلة', 'Family accounts') }}</span></a></li>
            @endif
        @endif

        @if(($entity ?? null) === 'admin')
            @php
                $adminHubActive = request()->routeIs(
                    'panel.admin.settings.*', 'panel.admin.currencies.*', 'panel.admin.countries.*',
                    'panel.admin.document.*', 'panel.admin.faqs.*', 'panel.admin.subscriptions.*',
                    'panel.admin.regions.*', 'panel.admin.service.*'
                );
            @endphp
            <li class="panel-nav__section">{{ textByLanguage('الإدارة', 'Administration') }}</li>
            @if($has('settings.index'))
                <li>
                    <a class="panel-nav__link {{ $adminHubActive ? 'is-active' : '' }}"
                       href="{{ route('panel.admin.settings.index') }}">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span>{{ textByLanguage('مركز الإعدادات', 'Settings hub') }}</span>
                    </a>
                </li>
            @endif
            @if($has('service.index'))
                <li>
                    <a class="panel-nav__link {{ request()->routeIs('panel.admin.service.*') ? 'is-active' : '' }}"
                       href="{{ route('panel.admin.service.index') }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>{{ textByLanguage('الخدمات', 'Services') }}</span>
                    </a>
                </li>
            @endif
            @if($has('leads.offices'))
                <li>
                    <a class="panel-nav__link {{ request()->routeIs('panel.admin.leads.offices') ? 'is-active' : '' }}"
                       href="{{ route('panel.admin.leads.offices') }}">
                        <i class="bi bi-building-add"></i>
                        <span>{{ textByLanguage('طلبات المكاتب', 'Office requests') }}</span>
                    </a>
                </li>
            @endif
            @if($has('leads.drivers'))
                {{-- Website driver LEADS (DriverJobApplication from the landing form) —
                     deliberately distinct from the in-app "Driver applications"
                     (driver-applications.index / DriverApplication) so the two
                     never get confused. --}}
                <li>
                    <a class="panel-nav__link {{ request()->routeIs('panel.admin.leads.drivers') ? 'is-active' : '' }}"
                       href="{{ route('panel.admin.leads.drivers') }}">
                        <i class="bi bi-globe"></i>
                        <span>{{ textByLanguage('طلبات السائقين (الموقع)', 'Driver leads (website)') }}</span>
                    </a>
                </li>
            @endif
        @endif

        @if($has('security.index'))
            <li class="panel-nav__section">{{ textByLanguage('حسابي', 'My account') }}</li>
            <li>
                <a class="panel-nav__link {{ request()->routeIs($r('security.index')) ? 'is-active' : '' }}" href="{{ route($r('security.index')) }}">
                    <i class="bi bi-shield-lock"></i><span>{{ textByLanguage('أمان الحساب', 'Account security') }}</span>
                </a>
            </li>
        @endif
    </ul>
</aside>
