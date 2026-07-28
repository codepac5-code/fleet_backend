@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <script>
        (function () { try { if (localStorage.getItem('panel-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {} })();
        function panelThemeIcons() { var dark = document.documentElement.getAttribute('data-theme') === 'dark'; var el = document.querySelector('[data-theme-icon]'); if (el) el.className = 'bi ' + (dark ? 'bi-sun-fill' : 'bi-moon-stars'); }
        function panelToggleTheme() {
            try {
                var d = document.documentElement, dark = d.getAttribute('data-theme') === 'dark';
                if (dark) { d.removeAttribute('data-theme'); localStorage.setItem('panel-theme', 'light'); }
                else { d.setAttribute('data-theme', 'dark'); localStorage.setItem('panel-theme', 'dark'); }
                panelThemeIcons();
            } catch (e) {}
        }
        document.addEventListener('DOMContentLoaded', panelThemeIcons);
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ config('app.name') }} — {{ __('auth.sign_in') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('panel/css/panel.css') }}" rel="stylesheet">

    <style>
        body.panel-auth {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
            background: linear-gradient(135deg, var(--p-primary), var(--p-primary-600));
        }
        .auth-card {
            width: 100%; max-width: 440px;
            background: var(--p-surface);
            border-radius: var(--p-radius);
            box-shadow: var(--p-shadow-lg);
            padding: 34px 30px;
        }
        .auth-brand { text-align: center; font-weight: 700; font-size: 1.6rem; color: var(--p-primary); margin-bottom: 4px; }
        .auth-brand span { color: var(--p-accent); }
        .auth-sub { text-align: center; color: var(--p-text-muted); margin-bottom: 22px; font-size: .92rem; }

        .role-switch { display: flex; gap: 8px; margin-bottom: 18px; }
        .role-switch button {
            flex: 1; padding: 10px 6px; border: 1.5px solid var(--p-border);
            background: #fff; color: var(--p-text-muted); border-radius: var(--p-radius-sm);
            font-family: inherit; font-weight: 600; font-size: .85rem; cursor: pointer;
            transition: var(--p-transition);
        }
        .role-switch button.active { border-color: var(--p-primary); background: var(--p-primary); color: #fff; }

        .field { margin-bottom: 15px; }
        .field label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 6px; color: var(--p-text); }
        .field input, .field select {
            width: 100%; padding: 11px 14px; font-family: inherit; font-size: .95rem;
            border: 1.5px solid var(--p-border); border-radius: var(--p-radius-sm); background: #fff; color: var(--p-text);
        }
        .field input:focus, .field select:focus { outline: none; border-color: var(--p-accent); box-shadow: 0 0 0 3px rgba(248,166,9,.15); }

        .auth-btn {
            width: 100%; padding: 12px; margin-top: 6px; border: none; cursor: pointer;
            background: var(--p-accent); color: #fff; font-family: inherit; font-weight: 700; font-size: 1rem;
            border-radius: var(--p-radius-sm); transition: var(--p-transition);
        }
        .auth-btn:hover { background: var(--p-accent-600); }

        .auth-errors {
            background: #fdecec; border: 1px solid #f5c2c2; color: #842029;
            border-radius: var(--p-radius-sm); padding: 10px 14px; margin-bottom: 16px; font-size: .88rem;
        }
        .auth-errors ul { margin: 0; padding-inline-start: 18px; }
        .remember { display: flex; align-items: center; gap: 8px; font-size: .88rem; color: var(--p-text-muted); }
        .remember input { width: auto; }
    </style>
</head>
<body class="panel panel-auth">
    <div class="auth-card">
        @php $cur = app()->getLocale(); $other = $cur === 'ar' ? 'en' : 'ar'; @endphp
        <div class="auth-tools">
            <button type="button" class="auth-theme" onclick="panelToggleTheme()" title="{{ textByLanguage('تبديل السمة', 'Toggle theme') }}"><i data-theme-icon class="bi bi-moon-stars"></i></button>
            <a href="{{ route('panel.locale', $other) }}" class="auth-lang"><i class="bi bi-translate"></i> {{ $other === 'ar' ? 'العربية' : 'English' }}</a>
        </div>
        <div class="auth-brand">{{ config('app.name') }}<span>.</span></div>
        <p class="auth-sub">{{ __('auth.login_continue') }}</p>

        @if ($errors->any())
            <div class="auth-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('panel.login.attempt') }}" autocomplete="off">
            @csrf
            <input type="hidden" name="role" id="roleInput" value="admin">

            <div class="role-switch" role="radiogroup" aria-label="{{ textByLanguage('نوع الحساب', 'Account type') }}">
                <button type="button" class="active" data-role="admin">{{ textByLanguage('مدير', 'Admin') }}</button>
                <button type="button" data-role="manager">{{ textByLanguage('مكتب', 'Office') }}</button>
                <button type="button" data-role="employee">{{ textByLanguage('موظف', 'Employee') }}</button>
            </div>

            <div class="field" id="regionField" style="display:none;">
                <label for="region">{{ textByLanguage('المنطقة', 'Region') }}</label>
                <select name="region" id="region">
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name ?? $country->name_en ?? $country->id }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="email">{{ __('auth.email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="field">
                <label for="password">{{ __('auth.login_password') }}</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">
            </div>

            <label class="remember field" id="rememberField">
                <input type="checkbox" name="remember" value="1">
                <span>{{ textByLanguage('تذكرني', 'Remember me') }}</span>
            </label>

            <button type="submit" class="auth-btn">{{ __('auth.login') }}</button>
        </form>
    </div>

    <script>
        (function () {
            var roleInput  = document.getElementById('roleInput');
            var regionWrap = document.getElementById('regionField');
            var rememberWrap = document.getElementById('rememberField');
            var buttons    = document.querySelectorAll('.role-switch button');

            function applyRole(role) {
                roleInput.value = role;
                regionWrap.style.display = role === 'admin' ? 'none' : 'block';
                rememberWrap.style.display = role === 'admin' ? 'flex' : 'none';
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    buttons.forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    applyRole(btn.getAttribute('data-role'));
                });
            });

            window.addEventListener('pageshow', function (e) {
                if (e.persisted) window.location.reload();
            });

            var form = document.querySelector('form');
            var submitting = false;
            form.addEventListener('submit', function (e) {
                if (submitting) return;
                e.preventDefault();
                submitting = true;
                fetch('{{ route('refresh-csrf') }}', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        var tokenInput = form.querySelector('input[name="_token"]');
                        if (data && data.token && tokenInput) tokenInput.value = data.token;
                    })
                    .catch(function () {})
                    .finally(function () { form.submit(); });
            });
        })();
    </script>
</body>
</html>
