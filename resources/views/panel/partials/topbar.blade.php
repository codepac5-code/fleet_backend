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
        @endif

        @php $cur = app()->getLocale(); $other = $cur === 'ar' ? 'en' : 'ar'; $nr = fn ($n) => "panel.{$entity}.{$n}"; @endphp

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
    </div>
</header>
<script>
    document.addEventListener('click', function (e) {
        var bell = document.getElementById('panelBell');
        if (bell && !bell.contains(e.target)) bell.classList.remove('open');
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
