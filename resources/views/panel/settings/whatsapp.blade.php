@extends('panel.layouts.master')

@section('title', textByLanguage('واتساب / إرسال OTP', 'WhatsApp / OTP'))
@section('page-title', textByLanguage('واتساب / إرسال OTP', 'WhatsApp / OTP'))

@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
@endphp

@push('styles')
<style>
    .set-card { background: var(--p-surface, #fff); border: 1px solid var(--p-border); border-radius: 16px; padding: 20px 22px; margin-bottom: 18px; }
    .set-card h3 { font-size: 1rem; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .set-hint { color: var(--p-text-muted); font-size: .85rem; margin: 0 0 16px; }
    .set-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .set-fld { display: flex; flex-direction: column; gap: 6px; }
    .set-fld.col2 { grid-column: 1 / -1; }
    .set-fld label { font-size: .82rem; font-weight: 600; color: var(--p-text); }
    .set-fld input { width: 100%; padding: 10px 12px; border: 1.5px solid var(--p-border); border-radius: var(--p-radius-sm); font-family: inherit; background: #fff; }
    .set-fld small { color: var(--p-text-muted); font-size: .78rem; }
    .set-flash { padding: 11px 14px; border-radius: 12px; margin-bottom: 16px; font-size: .9rem; }
    .set-flash.ok { background: rgba(26,127,55,.12); color: var(--p-success, #1a7f37); }
    .set-flash.bad { background: rgba(220,53,69,.12); color: var(--p-danger, #dc3545); }
    .set-save { padding: 11px 24px; border: none; background: var(--p-accent, var(--p-primary)); color: #fff; font-weight: 700; border-radius: var(--p-radius-sm); cursor: pointer; font-family: inherit; }
    .set-test { padding: 10px 20px; border: 1.5px solid var(--p-border); background: #fff; color: var(--p-text); font-weight: 600; border-radius: var(--p-radius-sm); cursor: pointer; font-family: inherit; }
    .set-test-row { display: flex; gap: .6rem; align-items: flex-end; flex-wrap: wrap; }
    @media (max-width: 860px) { .set-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    @if(session('status'))<div class="set-flash ok">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="set-flash bad">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="set-flash bad">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('panel.admin.settings.whatsapp.save') }}">
        @csrf
        <div class="set-card">
            <h3>{{ $t('Connection', 'الاتصال') }}</h3>
            <p class="set-hint">{{ $t('Saved values override the server environment without a redeploy. Leave the token blank to keep the stored one.', 'القيم المحفوظة تتجاوز إعدادات الخادم دون إعادة نشر. اترك التوكن فارغاً للإبقاء على المحفوظ.') }}</p>
            <div class="set-grid">
                <div class="set-fld col2">
                    <label>{{ $t('API base URL', 'رابط الواجهة') }}</label>
                    <input name="whatsapp_base_url" value="{{ old('whatsapp_base_url', $baseUrl) }}" placeholder="https://message.example.com">
                </div>
                <div class="set-fld">
                    <label>{{ $t('API path prefix', 'مسار الواجهة') }}</label>
                    <input name="whatsapp_prefix" value="{{ old('whatsapp_prefix', $prefix) }}" placeholder="whatsapp/api/v1">
                </div>
                <div class="set-fld">
                    <label>{{ $t('Session ID', 'معرّف الجلسة') }}</label>
                    <input name="whatsapp_session_id" value="{{ old('whatsapp_session_id', $sessionId) }}" placeholder="00000000-0000-...">
                </div>
                <div class="set-fld col2">
                    <label>{{ $t('API token', 'توكن الواجهة') }}</label>
                    <input type="password" name="whatsapp_token" autocomplete="off" placeholder="{{ $tokenHint ?? $t('Enter API token', 'أدخل التوكن') }}">
                    @if($tokenHint)<small>{{ $t('Currently set', 'مضبوط حالياً') }}: {{ $tokenHint }}</small>@endif
                </div>
            </div>
        </div>
        <button class="set-save" type="submit">{{ $t('Save', 'حفظ') }}</button>
    </form>

    <div class="set-card" style="margin-top:18px">
        <h3>{{ $t('Send a test message', 'إرسال رسالة تجريبية') }}</h3>
        <p class="set-hint">{{ $t('Uses the currently saved connection. Enter a phone number with country code.', 'يستخدم الاتصال المحفوظ حالياً. أدخل رقماً مع رمز الدولة.') }}</p>
        <form method="POST" action="{{ route('panel.admin.settings.whatsapp.test') }}">
            @csrf
            <div class="set-test-row">
                <div class="set-fld" style="flex:1; min-width:220px">
                    <label>{{ $t('Test phone', 'رقم الاختبار') }}</label>
                    <input name="test_phone" value="{{ old('test_phone') }}" placeholder="+9665...">
                </div>
                <button class="set-test" type="submit">{{ $t('Send test', 'إرسال') }}</button>
            </div>
        </form>
    </div>
@endsection
