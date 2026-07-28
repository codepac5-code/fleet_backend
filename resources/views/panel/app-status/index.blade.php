@extends('panel.layouts.master')

@section('title', textByLanguage('حالة التطبيق', 'App status'))
@section('page-title', textByLanguage('حالة التطبيق', 'App status'))

@php
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
@endphp

@push('styles')
<style>
    .as-card { background:var(--p-surface,#fff); border:1px solid var(--p-border); border-radius:16px; padding:20px 22px; margin-bottom:18px; }
    .as-card h3 { font-size:1rem; margin:0 0 12px; }
    .as-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .as-fld label { display:block; font-size:.82rem; font-weight:600; margin-bottom:6px; }
    .as-fld input, .as-fld textarea { width:100%; padding:10px 12px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm); font-family:inherit; }
    @media (max-width:720px){ .as-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('حالة التطبيق والإصدارات', 'App status & versions')"
        :subtitle="textByLanguage('وضع الصيانة والحد الأدنى للإصدار — لكل تطبيقات المنصّة', 'Maintenance mode and minimum version — for all platform apps')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('panel.admin.app-status.save') }}">
        @csrf

        <div class="as-card">
            <h3><i class="bi bi-cone-striped"></i> {{ $t('Maintenance mode', 'وضع الصيانة') }}</h3>
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:12px;">
                <input type="checkbox" name="maintenance" value="1" @checked($maintenance)>
                {{ $t('Put the apps into maintenance mode', 'تفعيل وضع الصيانة للتطبيقات') }}
            </label>
            <div class="as-fld">
                <label>{{ $t('Message shown to users', 'الرسالة المعروضة للمستخدمين') }}</label>
                <textarea name="maintenance_message" rows="2">{{ old('maintenance_message', $maintenance_message) }}</textarea>
            </div>
        </div>

        <div class="as-card">
            <h3><i class="bi bi-android2"></i> {{ $t('Android', 'أندرويد') }}</h3>
            <div class="as-grid">
                <div class="as-fld"><label>{{ $t('Minimum version (force update below)', 'الحد الأدنى (تحديث إجباري تحته)') }}</label><input name="android_min" value="{{ old('android_min', $android_min) }}" placeholder="1.4.0"></div>
                <div class="as-fld"><label>{{ $t('Latest version', 'أحدث إصدار') }}</label><input name="android_latest" value="{{ old('android_latest', $android_latest) }}" placeholder="1.5.2"></div>
            </div>
        </div>

        <div class="as-card">
            <h3><i class="bi bi-apple"></i> {{ $t('iOS', 'آي أو إس') }}</h3>
            <div class="as-grid">
                <div class="as-fld"><label>{{ $t('Minimum version', 'الحد الأدنى') }}</label><input name="ios_min" value="{{ old('ios_min', $ios_min) }}" placeholder="1.4.0"></div>
                <div class="as-fld"><label>{{ $t('Latest version', 'أحدث إصدار') }}</label><input name="ios_latest" value="{{ old('ios_latest', $ios_latest) }}" placeholder="1.5.2"></div>
            </div>
        </div>

        <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-floppy-disk"></i> {{ $t('Save', 'حفظ') }}</button>
    </form>

    <p style="color:var(--p-text-muted); font-size:.82rem; margin-top:14px;">
        <i class="bi bi-link-45deg"></i> {{ $t('Apps poll', 'التطبيقات تستفتي') }}: <code>{{ url('/content/app-status') }}</code>
    </p>

@endsection
