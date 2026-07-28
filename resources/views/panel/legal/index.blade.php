@extends('panel.layouts.master')

@section('title', textByLanguage('المحتوى القانوني', 'Legal content'))
@section('page-title', textByLanguage('المحتوى القانوني', 'Legal content'))

@php
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
@endphp

@push('styles')
<style>
    .lg-card { background:var(--p-surface,#fff); border:1px solid var(--p-border); border-radius:16px; padding:20px 22px; margin-bottom:18px; }
    .lg-card h3 { font-size:1rem; margin:0 0 12px; }
    .lg-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .lg-fld label { display:block; font-size:.82rem; font-weight:600; margin-bottom:6px; }
    .lg-fld textarea { width:100%; padding:10px 12px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm); font-family:inherit; }
    @media (max-width:820px){ .lg-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('الشروط وسياسة الخصوصية', 'Terms & privacy policy')"
        :subtitle="textByLanguage('نصوص تُعرَض في التطبيقات والموقع (لكل المنصّة)', 'Text shown in the apps and website (platform-wide)')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('panel.admin.legal.save') }}">
        @csrf

        <div class="lg-card">
            <h3><i class="bi bi-file-earmark-text"></i> {{ $t('Terms & Conditions', 'الشروط والأحكام') }}</h3>
            <div class="lg-grid">
                <div class="lg-fld"><label>{{ $t('English', 'الإنجليزية') }}</label><textarea name="terms_en" rows="10">{{ old('terms_en', $terms_en) }}</textarea></div>
                <div class="lg-fld"><label>{{ $t('Arabic', 'العربية') }}</label><textarea name="terms_ar" rows="10" dir="rtl">{{ old('terms_ar', $terms_ar) }}</textarea></div>
            </div>
        </div>

        <div class="lg-card">
            <h3><i class="bi bi-shield-lock"></i> {{ $t('Privacy Policy', 'سياسة الخصوصية') }}</h3>
            <div class="lg-grid">
                <div class="lg-fld"><label>{{ $t('English', 'الإنجليزية') }}</label><textarea name="privacy_en" rows="10">{{ old('privacy_en', $privacy_en) }}</textarea></div>
                <div class="lg-fld"><label>{{ $t('Arabic', 'العربية') }}</label><textarea name="privacy_ar" rows="10" dir="rtl">{{ old('privacy_ar', $privacy_ar) }}</textarea></div>
            </div>
        </div>

        <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-floppy-disk"></i> {{ $t('Save', 'حفظ') }}</button>
    </form>

@endsection
