@extends('panel.layouts.master')

@section('title', textByLanguage('إعدادات الدفع', 'Payment settings'))
@section('page-title', textByLanguage('إعدادات الدفع', 'Payment settings'))

@php
    $ar = app()->getLocale() === 'ar';
    $t = fn($en, $arText) => $ar ? $arText : $en;
    $webhookUrl = url('/webhooks/subscriptions/stripe');
    $paymentUrl = url('/webhooks/payments/stripe');
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
    @media (max-width: 860px) { .set-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    @if(session('status'))<div class="set-flash ok">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="set-flash bad">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('panel.admin.settings.payments.save') }}">
        @csrf

        <div class="set-card">
            <h3>{{ $t('Stripe keys', 'مفاتيح Stripe') }}</h3>
            <p class="set-hint">{{ $t('Saved keys override the server environment without a redeploy. Leave a secret field blank to keep the stored value.', 'المفاتيح المحفوظة تتجاوز إعدادات الخادم دون إعادة نشر. اترك حقل السر فارغاً للإبقاء على القيمة المحفوظة.') }}</p>
            <div class="set-grid">
                <div class="set-fld col2">
                    <label>{{ $t('Publishable key', 'المفتاح العام') }}</label>
                    <input name="stripe_public" value="{{ old('stripe_public', $publicKey) }}" placeholder="pk_live_...">
                </div>
                <div class="set-fld">
                    <label>{{ $t('Secret key', 'المفتاح السري') }}</label>
                    <input type="password" name="stripe_secret" autocomplete="off" placeholder="{{ $secretHint ?? 'sk_live_...' }}">
                    @if($secretHint)<small>{{ $t('Currently set', 'مضبوط حالياً') }}: {{ $secretHint }}</small>@endif
                </div>
                <div class="set-fld">
                    <label>{{ $t('Webhook signing secret', 'سر توقيع الـWebhook') }}</label>
                    <input type="password" name="stripe_webhook_secret" autocomplete="off" placeholder="{{ $webhookHint ?? 'whsec_...' }}">
                    @if($webhookHint)<small>{{ $t('Currently set', 'مضبوط حالياً') }}: {{ $webhookHint }}</small>@endif
                </div>
            </div>
        </div>

        <div class="set-card">
            <h3>{{ $t('Webhook endpoints', 'روابط الـWebhook') }}</h3>
            <p class="set-hint">{{ $t('Register these URLs in the Stripe dashboard so payments and subscriptions stay in sync.', 'سجّل هذه الروابط في لوحة Stripe حتى تبقى المدفوعات والاشتراكات متزامنة.') }}</p>
            <div class="set-grid">
                <div class="set-fld col2">
                    <label>{{ $t('Subscriptions', 'الاشتراكات') }}</label>
                    <input value="{{ $webhookUrl }}" readonly onclick="this.select()">
                </div>
                <div class="set-fld col2">
                    <label>{{ $t('Payments / wallet top-ups', 'المدفوعات / شحن المحفظة') }}</label>
                    <input value="{{ $paymentUrl }}" readonly onclick="this.select()">
                </div>
            </div>
        </div>

        <button class="set-save" type="submit">{{ $t('Save', 'حفظ') }}</button>
    </form>
@endsection
