@php
    $entity = $entity ?? 'admin';
    $isRtl  = app()->getLocale() === 'ar';
    $embed  = request()->boolean('embed');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <script>
        (function () { try { if (localStorage.getItem('panel-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {} })();
        function panelToggleTheme() {
            try {
                var d = document.documentElement, dark = d.getAttribute('data-theme') === 'dark';
                if (dark) { d.removeAttribute('data-theme'); localStorage.setItem('panel-theme', 'light'); }
                else { d.setAttribute('data-theme', 'dark'); localStorage.setItem('panel-theme', 'dark'); }
                panelThemeIcons();
            } catch (e) {}
        }
        function panelThemeIcons() {
            var dark = document.documentElement.getAttribute('data-theme') === 'dark';
            document.querySelectorAll('[data-theme-icon]').forEach(function (el) { el.className = 'bi ' + (dark ? 'bi-sun-fill' : 'bi-moon-stars'); });
        }
        document.addEventListener('DOMContentLoaded', panelThemeIcons);
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ config('app.url') }}">
    <title>@yield('title', config('app.name')) — Panel</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('panel/css/panel.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="panel {{ $embed ? 'panel--embed' : '' }}">
    @if($embed)
        <main class="panel-content panel-content--embed" style="padding:18px 20px 40px;">
            @yield('content')
        </main>
        <script>
        (function () {
            // Keep embedded navigation inside the frame (propagate ?embed=1)
            function withEmbed(url) {
                try { var u = new URL(url, location.href); if (u.origin === location.origin) { u.searchParams.set('embed', '1'); return u.pathname + u.search + u.hash; } } catch (e) {}
                return url;
            }
            document.addEventListener('click', function (e) {
                var a = e.target.closest('a[href]');
                if (!a) return;
                var href = a.getAttribute('href');
                if (!href || href.charAt(0) === '#' || /^(mailto:|tel:|javascript:)/i.test(href) || a.target === '_blank') return;
                a.setAttribute('href', withEmbed(href));
            }, true);
            document.addEventListener('submit', function (e) {
                var f = e.target;
                if (!f || f.method && f.method.toLowerCase() === 'get') { if (f.action) f.action = withEmbed(f.action); return; }
                if (!f.querySelector('input[name="embed"]')) {
                    var i = document.createElement('input'); i.type = 'hidden'; i.name = 'embed'; i.value = '1'; f.appendChild(i);
                }
            }, true);
            // Report height to the parent so the iframe can auto-size. Only post
            // when the height ACTUALLY changed — otherwise animations (count-up,
            // spinners, hover/value-flash) fired a resize on every frame and the
            // page kept jumping on its own. Watch structural changes only (NOT
            // attributes) and coalesce bursts into one measurement per frame.
            var lastReported = 0, rafPending = false;
            function reportHeight() {
                var h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
                if (Math.abs(h - lastReported) < 2) return;
                lastReported = h;
                try { parent.postMessage({ __panelEmbedHeight: h }, '*'); } catch (e) {}
            }
            function scheduleReport() {
                if (rafPending) return;
                rafPending = true;
                requestAnimationFrame(function () { rafPending = false; reportHeight(); });
            }
            window.addEventListener('load', reportHeight);
            window.addEventListener('resize', scheduleReport);
            new MutationObserver(scheduleReport).observe(document.body, { childList: true, subtree: true });
            setInterval(reportHeight, 1000);
        })();
        </script>
        @include('panel.partials.realtime')
        @stack('scripts')
    @else
    <div class="panel-shell" id="panelShell">

        @include('panel.partials.sidebar', ['entity' => $entity])

        <div class="panel-backdrop" id="panelBackdrop" onclick="panelCloseNav()" aria-hidden="true"></div>

        <div class="panel-main">
            @include('panel.partials.topbar', ['entity' => $entity])

            <main class="panel-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function () {
            var shell = document.getElementById('panelShell');
            window.panelToggleNav = function () { if (shell) shell.classList.toggle('nav-open'); };
            window.panelCloseNav = function () { if (shell) shell.classList.remove('nav-open'); };
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') panelCloseNav(); });
            var side = document.querySelector('.panel-sidebar');
            if (side) {
                side.addEventListener('click', function (e) {
                    if (e.target.closest('.panel-nav__link') && window.matchMedia('(max-width: 992px)').matches) panelCloseNav();
                });
            }
        })();
    </script>

    @include('panel.partials.realtime')
    @stack('scripts')
    @endif
</body>
</html>
