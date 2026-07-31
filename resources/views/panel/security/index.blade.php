@extends('panel.layouts.master')

@section('title', textByLanguage('أمان الحساب', 'Account security'))
@section('page-title', textByLanguage('أمان الحساب', 'Account security'))

@php $r = fn ($n) => "panel.{$entity}.{$n}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('التحقق بخطوتين', 'Two-factor authentication')"
        :subtitle="textByLanguage('رمز مؤقت من تطبيق المصادقة يُطلب بعد كلمة المرور عند كل تسجيل دخول', 'A time-based code from your authenticator app, asked for after your password on every sign-in')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    @if($recoveryCodes)
        <div class="p-card" style="margin-bottom:16px;border:2px solid var(--p-accent);">
            <h3 style="font-size:.95rem;font-weight:800;margin:0 0 6px;"><i class="bi bi-key"></i> {{ textByLanguage('رموز الاسترداد', 'Recovery codes') }}</h3>
            <p style="font-size:.85rem;color:var(--p-text-muted);margin:0 0 12px;">
                {{ textByLanguage('احفظ هذه الرموز في مكان آمن — كل رمز يُستخدم مرة واحدة، ولن تظهر مجدداً.', 'Save these somewhere safe — each works once, and they are not shown again.') }}
            </p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;">
                @foreach($recoveryCodes as $code)
                    <code style="padding:8px;background:var(--p-bg);border-radius:var(--p-radius-sm);text-align:center;letter-spacing:.06em;" dir="ltr">{{ $code }}</code>
                @endforeach
            </div>
        </div>
    @endif

    <div class="p-card">
        @if($enabled)
            <p style="margin:0 0 14px;">
                <x-panel.badge tone="success"><i class="bi bi-shield-check"></i> {{ textByLanguage('مفعّل', 'Enabled') }}</x-panel.badge>
                <span style="margin-inline-start:10px;font-size:.85rem;color:var(--p-text-muted);">
                    {{ textByLanguage('رموز استرداد متبقية:', 'Recovery codes left:') }} {{ $recoveryLeft }}
                </span>
            </p>

            @if($required)
                <p class="p-empty" style="text-align:start;"><i class="bi bi-lock"></i>
                    {{ textByLanguage('التحقق بخطوتين إلزامي لحسابك بقرار من إدارة المنصّة.', 'Two-factor authentication is mandatory for your account by platform policy.') }}</p>
            @else
                <form method="POST" action="{{ route($r('security.two-factor.disable')) }}" style="display:flex;gap:10px;align-items:end;max-width:420px;">
                    @csrf
                    <div style="flex:1;">
                        <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('رمز التحقق الحالي', 'Current code') }}</label>
                        <input name="code" required inputmode="text" placeholder="123456" style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                    </div>
                    <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-shield-slash"></i> {{ textByLanguage('إيقاف', 'Turn off') }}</button>
                </form>
            @endif

        @elseif($pending)
            <h3 style="font-size:.95rem;font-weight:800;margin:0 0 10px;">{{ textByLanguage('أضف الحساب إلى تطبيق المصادقة', 'Add the account to your authenticator app') }}</h3>
            <ol style="font-size:.87rem;line-height:1.9;padding-inline-start:18px;margin:0 0 14px;">
                <li>{{ textByLanguage('افتح Google Authenticator أو أي تطبيق مماثل.', 'Open Google Authenticator or any similar app.') }}</li>
                <li>{{ textByLanguage('اختر «إدخال مفتاح الإعداد» وألصق المفتاح التالي:', 'Choose “Enter a setup key” and paste the key below:') }}</li>
            </ol>

            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
                <code id="twoFactorSecret" style="padding:10px 14px;background:var(--p-bg);border-radius:var(--p-radius-sm);font-size:1rem;letter-spacing:.12em;" dir="ltr">{{ $pending['formatted'] }}</code>
                <button type="button" class="p-btn p-btn--soft" data-copy="{{ $pending['secret'] }}"><i class="bi bi-clipboard"></i> {{ textByLanguage('نسخ المفتاح', 'Copy key') }}</button>
                <button type="button" class="p-btn p-btn--soft" data-copy="{{ $pending['uri'] }}"><i class="bi bi-link-45deg"></i> {{ textByLanguage('نسخ رابط otpauth', 'Copy otpauth link') }}</button>
            </div>

            <form method="POST" action="{{ route($r('security.two-factor.confirm')) }}" style="display:flex;gap:10px;align-items:end;max-width:420px;">
                @csrf
                <div style="flex:1;">
                    <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('الرمز المعروض في التطبيق', 'The code shown in the app') }}</label>
                    <input name="code" required inputmode="numeric" placeholder="123456" autofocus style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                </div>
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('تفعيل', 'Turn on') }}</button>
            </form>

        @else
            <p style="margin:0 0 14px;">
                <x-panel.badge tone="gray"><i class="bi bi-shield-slash"></i> {{ textByLanguage('غير مفعّل', 'Not enabled') }}</x-panel.badge>
                @if($required)
                    <span style="margin-inline-start:10px;font-size:.85rem;color:#b91c1c;font-weight:700;">
                        {{ textByLanguage('مطلوب تفعيله لحسابك', 'Required for your account') }}
                    </span>
                @endif
            </p>
            <form method="POST" action="{{ route($r('security.two-factor.start')) }}">
                @csrf
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-shield-lock"></i> {{ textByLanguage('تفعيل التحقق بخطوتين', 'Set up two-factor authentication') }}</button>
            </form>
        @endif
    </div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(btn.dataset.copy);
        var label = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(function () { btn.innerHTML = label; }, 1200);
    });
});
</script>
@endpush
