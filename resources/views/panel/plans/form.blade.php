@extends('panel.layouts.master')

@section('title', textByLanguage('خطة اشتراك', 'Subscription plan'))
@section('page-title', textByLanguage('خطة اشتراك', 'Subscription plan'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
    $editing = $plan !== null;
    $v = fn ($field, $default = '') => old($field, $plan?->{$field} ?? $default);
    $action = $editing ? route($r('plans.update'), $plan->id) : route($r('plans.store'));
@endphp

@push('styles')
<style>
    .pl-card { background:var(--p-surface,#fff); border:1px solid var(--p-border); border-radius:16px; padding:20px 22px; margin-bottom:18px; }
    .pl-card h3 { font-size:1rem; margin:0 0 14px; }
    .pl-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
    .pl-fld { display:flex; flex-direction:column; gap:6px; }
    .pl-fld label { font-size:.82rem; font-weight:600; }
    .pl-fld input { width:100%; padding:9px 11px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm); font-family:inherit; }
    .pl-fld small { color:var(--p-text-muted); font-size:.75rem; }
    @media (max-width:760px){ .pl-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar :title="$editing ? ($plan->name) : $t('New plan', 'خطة جديدة')" :subtitle="$t('Office subscription plan', 'خطة اشتراك مكتب')">
        <x-slot:actions>
            <a href="{{ route($r('plans.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $ar ? 'right' : 'left' }}"></i> {{ $t('Back', 'رجوع') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="pl-card">
            <h3>{{ $t('Basics', 'الأساسيات') }}</h3>
            <div class="pl-grid">
                <div class="pl-fld"><label>{{ $t('Key (unique)', 'المفتاح (فريد)') }}</label><input name="key" value="{{ $v('key') }}" required pattern="[a-z0-9_]+" placeholder="business"><small>{{ $t('lowercase, no spaces', 'حروف صغيرة بلا فراغ') }}</small></div>
                <div class="pl-fld"><label>{{ $t('Name', 'الاسم') }}</label><input name="name" value="{{ $v('name') }}" required></div>
                <div class="pl-fld"><label>{{ $t('Sort', 'الترتيب') }}</label><input name="sort" type="number" value="{{ $v('sort', 0) }}"></div>
            </div>
        </div>

        <div class="pl-card">
            <h3>{{ $t('Price & commission', 'السعر والعمولة') }}</h3>
            <div class="pl-grid">
                <div class="pl-fld"><label>{{ $t('Monthly price (minor)', 'السعر الشهري (وحدة صغرى)') }}</label><input name="price_minor" type="number" min="0" value="{{ $v('price_minor') }}"><small>{{ $t('empty = custom/enterprise', 'فارغ = مخصّص') }}</small></div>
                <div class="pl-fld"><label>{{ $t('Currency', 'العملة') }}</label><input name="currency_code" value="{{ $v('currency_code') }}" maxlength="3" placeholder="USD"></div>
                <div class="pl-fld"><label>{{ $t('Fleet commission %', 'عمولة الأسطول %') }}</label><input name="fleet_commission_rate" type="number" step="0.1" min="0" max="100" value="{{ $v('fleet_commission_rate') }}"></div>
            </div>
        </div>

        <div class="pl-card">
            <h3>{{ $t('Limits & overage', 'الحدود ورسوم التجاوز') }}</h3>
            <div class="pl-grid">
                <div class="pl-fld"><label>{{ $t('Driver limit', 'حد السائقين') }}</label><input name="driver_limit" type="number" min="0" value="{{ $v('driver_limit') }}"><small>{{ $t('empty = unlimited', 'فارغ = بلا حد') }}</small></div>
                <div class="pl-fld"><label>{{ $t('Monthly ride limit', 'حد الرحلات الشهري') }}</label><input name="ride_limit" type="number" min="0" value="{{ $v('ride_limit') }}"><small>{{ $t('empty = unlimited', 'فارغ = بلا حد') }}</small></div>
                <div class="pl-fld"><label>{{ $t('Trial days', 'أيام التجربة') }}</label><input name="trial_days" type="number" min="0" value="{{ $v('trial_days', 0) }}"></div>
                <div class="pl-fld"><label>{{ $t('Extra ride price (minor)', 'سعر الرحلة الزائدة') }}</label><input name="extra_ride_minor" type="number" min="0" value="{{ $v('extra_ride_minor') }}"><small>{{ $t('charged per ride over the limit', 'تُحتسب لكل رحلة فوق الحد') }}</small></div>
                <div class="pl-fld"><label>{{ $t('Extra driver price (minor)', 'سعر السائق الزائد') }}</label><input name="extra_driver_minor" type="number" min="0" value="{{ $v('extra_driver_minor') }}"><small>{{ $t('charged per driver over the limit', 'تُحتسب لكل سائق فوق الحد') }}</small></div>
            </div>
        </div>

        <div class="pl-card">
            <label style="display:flex;align-items:center;gap:8px;font-weight:600;margin-bottom:10px;"><input type="checkbox" name="is_active" value="1" @checked($v('is_active', 1))> {{ $t('Active (offerable to offices)', 'مفعّلة (قابلة للإسناد)') }}</label>
            <label style="display:flex;align-items:center;gap:8px;font-weight:600;"><input type="checkbox" name="is_popular" value="1" @checked($v('is_popular', 0))> {{ $t('Mark as popular', 'وسمها كشائعة') }}</label>
        </div>

        <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-floppy-disk"></i> {{ $t('Save plan', 'حفظ الخطة') }}</button>
    </form>

@endsection
