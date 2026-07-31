@php $isRtl = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <script>
        (function () { try { if (localStorage.getItem('panel-theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark'); } catch (e) {} })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — {{ textByLanguage('التحقق بخطوتين', 'Two-factor authentication') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('panel/css/panel.css') }}" rel="stylesheet">

    <style>
        body.panel-auth {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 24px;
            background: linear-gradient(135deg, var(--p-primary), var(--p-primary-600));
        }
        .auth-card { width: 100%; max-width: 420px; background: var(--p-surface); border-radius: var(--p-radius); box-shadow: var(--p-shadow-lg); padding: 34px 30px; }
        .auth-brand { text-align: center; font-weight: 700; font-size: 1.5rem; color: var(--p-primary); }
        .auth-sub { text-align: center; color: var(--p-text-muted); margin: 6px 0 22px; font-size: .92rem; }
        .code-input {
            width: 100%; padding: 14px; text-align: center; letter-spacing: .5em; font-size: 1.4rem; font-weight: 700;
            border: 1.5px solid var(--p-border); border-radius: var(--p-radius-sm); background: var(--p-surface); color: var(--p-text);
        }
        .code-input:focus { outline: none; border-color: var(--p-accent); box-shadow: 0 0 0 3px rgba(248,166,9,.15); }
        .auth-btn { width: 100%; margin-top: 16px; padding: 12px; border: 0; border-radius: var(--p-radius-sm); background: var(--p-primary); color: #fff; font-weight: 700; cursor: pointer; }
        .auth-back { display: block; text-align: center; margin-top: 14px; font-size: .85rem; color: var(--p-text-muted); }
        .auth-errors { background: rgba(220,38,38,.08); color: #b91c1c; border-radius: var(--p-radius-sm); padding: 10px 12px; margin-bottom: 14px; font-size: .85rem; }
    </style>
</head>
<body class="panel panel-auth">
    <div class="auth-card">
        <div class="auth-brand"><i class="bi bi-shield-lock"></i></div>
        <p class="auth-sub">{{ textByLanguage('أدخل الرمز من تطبيق المصادقة، أو أحد رموز الاسترداد.', 'Enter the code from your authenticator app, or one of your recovery codes.') }}</p>

        @if ($errors->any())
            <div class="auth-errors">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('panel.two-factor.verify') }}" autocomplete="off">
            @csrf
            <input type="text" name="code" class="code-input" inputmode="text" autocomplete="one-time-code" autofocus required>
            <button type="submit" class="auth-btn">{{ textByLanguage('تأكيد', 'Verify') }}</button>
        </form>

        <a href="{{ route('panel.login') }}" class="auth-back">{{ textByLanguage('العودة لتسجيل الدخول', 'Back to sign in') }}</a>
    </div>
</body>
</html>
