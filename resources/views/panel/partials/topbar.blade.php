<header class="panel-topbar">
    <button type="button" class="panel-burger" onclick="panelToggleNav()" aria-label="{{ textByLanguage('القائمة', 'Menu') }}">
        <i class="bi bi-list"></i>
    </button>
    <div class="panel-topbar__title">
        <strong>@yield('page-title', __('messages.dashboard'))</strong>
    </div>

    <div class="panel-topbar__tools">

        @if(($entity ?? null) === 'admin' && isset($panelCountries) && $panelCountries->count())
            <form method="POST" action="{{ route('panel.admin.switch-country') }}"
                  id="countrySwitchForm" class="panel-country-switch">
                @csrf
                <i class="bi bi-globe2"></i>
                <select name="country_id" aria-label="Country"
                        onchange="document.getElementById('countrySwitchForm').submit()">
                    <option value="{{ $allCountriesScope ?? 'all' }}"
                        @selected((string) $activeCountryId === (string) ($allCountriesScope ?? 'all'))>
                        🌍 {{ textByLanguage('كل الدول', 'All countries') }}
                    </option>
                    @foreach($panelCountries as $country)
                        <option value="{{ $country->id }}"
                            @selected((string) $activeCountryId === (string) $country->id)>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            @if(!empty($sharedDatabaseGroups))
                @php
                    $sharedLabel = collect($sharedDatabaseGroups)->map(fn ($names) => implode(' + ', $names))->implode('، ');
                @endphp
                <span class="panel-shared-db" title="{{ textByLanguage(
                        'هذه الدول مُعدّة على قاعدة بيانات واحدة، فبياناتها مشتركة و«كل الدول» يحسب صفوفها مرة واحدة: ' . $sharedLabel,
                        'These countries are configured on one database, so they share data and “All countries” counts their rows once: ' . $sharedLabel
                    ) }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>{{ $sharedLabel }}</span>
                </span>
            @endif
        @endif

        @php $cur = app()->getLocale(); $other = $cur === 'ar' ? 'en' : 'ar'; $nr = fn ($n) => "panel.{$entity}.{$n}"; @endphp

        @if(!empty($panelBilling) && \Illuminate\Support\Facades\Route::has($nr('subscription.show')))
            <a class="panel-billing panel-billing--{{ $panelBilling['tone'] }}" href="{{ route($nr('subscription.show')) }}">
                @if($panelBilling['status'] === 'trialing')
                    <i class="bi bi-hourglass-split"></i>
                    <span>{{ textByLanguage('التجربة', 'Trial') }}: {{ $panelBilling['days'] }} {{ textByLanguage('يوم', 'd') }}</span>
                @elseif($panelBilling['status'] === 'past_due')
                    <i class="bi bi-exclamation-octagon"></i>
                    <span>{{ textByLanguage('دفعة متعثّرة', 'Payment failed') }}</span>
                @else
                    <i class="bi bi-award"></i>
                    <span>{{ textByLanguage('بلا اشتراك', 'No subscription') }}</span>
                @endif
            </a>
        @endif

        <div class="panel-bell" id="panelBell">
            <button type="button" class="panel-bell__btn" onclick="document.getElementById('panelBell').classList.toggle('open')" title="{{ textByLanguage('الإشعارات', 'Notifications') }}">
                <i class="bi bi-bell"></i>
                @if(($panelNotifUnread ?? 0) > 0)<span class="panel-bell__badge">{{ $panelNotifUnread > 9 ? '9+' : $panelNotifUnread }}</span>@endif
            </button>
            <div class="panel-bell__menu">
                <div class="panel-bell__head">
                    <strong>{{ textByLanguage('الإشعارات', 'Notifications') }}</strong>
                    @if(($panelNotifUnread ?? 0) > 0 && \Illuminate\Support\Facades\Route::has($nr('notifications.read')))
                        <form method="POST" action="{{ route($nr('notifications.read')) }}">@csrf<button type="submit" class="panel-bell__mark">{{ textByLanguage('تعليم الكل كمقروء', 'Mark all read') }}</button></form>
                    @endif
                </div>
                <div class="panel-bell__list">
                    @forelse(($panelNotifications ?? []) as $n)
                        <a class="panel-bell__item {{ $n['unread'] ? 'is-unread' : '' }}" href="{{ $n['link'] ?? '#' }}">
                            <span class="panel-bell__icon p-badge--{{ $n['tone'] }}"><i class="bi {{ $n['icon'] }}"></i></span>
                            <span class="panel-bell__body"><strong>{{ $n['title'] }}</strong><span>{{ $n['body'] }}</span><time>{{ $n['ago'] }}</time></span>
                        </a>
                    @empty
                        <div class="panel-bell__empty"><i class="bi bi-bell-slash"></i> {{ textByLanguage('لا توجد إشعارات', 'No notifications') }}</div>
                    @endforelse
                </div>
                @if(\Illuminate\Support\Facades\Route::has($nr('notifications.index')))
                    <a class="panel-bell__all" href="{{ route($nr('notifications.index')) }}">{{ textByLanguage('عرض كل الإشعارات', 'View all notifications') }}</a>
                @endif
            </div>
        </div>

        <button type="button" class="panel-theme-btn panel-fs-hide" id="panelFsBtn" onclick="panelToggleFullscreen()" title="{{ textByLanguage('ملء الشاشة', 'Fullscreen') }}">
            <i id="panelFsIcon" class="bi bi-arrows-fullscreen"></i>
        </button>
        <button type="button" class="panel-theme-btn" onclick="panelToggleTheme()" title="{{ textByLanguage('تبديل السمة', 'Toggle theme') }}">
            <i data-theme-icon class="bi bi-moon-stars"></i>
        </button>
        <a href="{{ route('panel.locale', $other) }}" class="panel-lang" title="{{ textByLanguage('تغيير اللغة', 'Change language') }}">
            <i class="bi bi-translate"></i>
            <span>{{ $other === 'ar' ? 'العربية' : 'English' }}</span>
        </a>

        @php
            $me = auth()->guard($entity)->user();
            $myName = $me->displayName ?? $me->officeName ?? trim(($me->firstName ?? '') . ' ' . ($me->lastName ?? ''));
            $myEmail = $me->email ?? $me->phoneNumber ?? null;
            $roleLabel = ['admin' => textByLanguage('مدير النظام', 'Super Admin'), 'office' => textByLanguage('مكتب', 'Office'), 'employee' => textByLanguage('موظف', 'Employee')][$entity] ?? $entity;
        @endphp
        <div class="panel-account" id="panelAccount">
            <button type="button" class="panel-account__btn" onclick="document.getElementById('panelAccount').classList.toggle('open')" title="{{ textByLanguage('حسابي', 'My account') }}">
                <span class="panel-account__initial">{{ mb_substr($myName ?: $roleLabel, 0, 1) }}</span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="panel-account__menu">
                <div class="panel-account__head">
                    <strong>{{ $myName ?: $roleLabel }}</strong>
                    <span>{{ $myEmail ?: $roleLabel }}</span>
                </div>
                @if(\Illuminate\Support\Facades\Route::has($nr('security.index')))
                    <a class="panel-account__item" href="{{ route($nr('security.index')) }}">
                        <i class="bi bi-shield-lock"></i> {{ textByLanguage('أمان الحساب', 'Account security') }}
                    </a>
                @endif
                @if(\Illuminate\Support\Facades\Route::has($nr('notifications.index')))
                    <a class="panel-account__item" href="{{ route($nr('notifications.index')) }}">
                        <i class="bi bi-bell"></i> {{ textByLanguage('الإشعارات', 'Notifications') }}
                    </a>
                @endif
                <form method="POST" action="{{ route('panel.logout') }}">
                    @csrf
                    <button type="submit" class="panel-account__item panel-account__item--danger">
                        <i class="bi bi-box-arrow-right"></i> {{ textByLanguage('تسجيل الخروج', 'Sign out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
<script>
    document.addEventListener('click', function (e) {
        var bell = document.getElementById('panelBell');
        if (bell && !bell.contains(e.target)) bell.classList.remove('open');

        var account = document.getElementById('panelAccount');
        if (account && !account.contains(e.target)) account.classList.remove('open');
    });

    function panelToggleFullscreen() {
        var doc = document, el = doc.documentElement;
        try {
            if (!doc.fullscreenElement && !doc.webkitFullscreenElement) {
                (el.requestFullscreen || el.webkitRequestFullscreen || function () {}).call(el);
            } else {
                (doc.exitFullscreen || doc.webkitExitFullscreen || function () {}).call(doc);
            }
        } catch (e) {}
    }

    function panelSyncFsIcon() {
        var on = !!(document.fullscreenElement || document.webkitFullscreenElement);
        var i = document.getElementById('panelFsIcon');
        if (i) i.className = 'bi ' + (on ? 'bi-fullscreen-exit' : 'bi-arrows-fullscreen');
        var b = document.getElementById('panelFsBtn');
        if (b) b.classList.toggle('is-on', on);
    }
    document.addEventListener('fullscreenchange', panelSyncFsIcon);
    document.addEventListener('webkitfullscreenchange', panelSyncFsIcon);
</script>
