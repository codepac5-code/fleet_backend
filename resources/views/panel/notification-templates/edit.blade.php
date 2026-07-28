@extends('panel.layouts.master')

@section('title', textByLanguage('تعديل القالب', 'Edit template'))
@section('page-title', textByLanguage('تعديل القالب', 'Edit template'))

@php
    $r = fn ($name) => "panel.admin.{$name}";
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
    $allChannels = ['inapp', 'push', 'email'];
    // Placeholders present in the default body — the ones the renderer fills.
    preg_match_all('/\{(\w+)\}/', implode(' ', $def['body'] ?? []), $m);
    $placeholders = array_values(array_unique($m[1] ?? []));
@endphp

@push('styles')
<style>
    .nt-fld { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
    .nt-fld label { font-size:.82rem; font-weight:600; }
    .nt-fld input, .nt-fld textarea { width:100%; padding:10px 12px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm); font-family:inherit; }
    .nt-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media (max-width:820px){ .nt-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar :title="$key" :subtitle="textByLanguage('قالب إشعار', 'Notification template')">
        <x-slot:actions>
            <a href="{{ route($r('notification-templates.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $ar ? 'right' : 'left' }}"></i> {{ $t('Back', 'رجوع') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    @if($placeholders)
        <div class="p-card" style="margin-bottom:16px;">
            <strong>{{ $t('Available placeholders', 'المتغيّرات المتاحة') }}:</strong>
            @foreach($placeholders as $ph)<code style="margin:0 4px;">{{ '{' . $ph . '}' }}</code>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route($r('notification-templates.save'), $key) }}">
        @csrf
        <div class="p-card">
            <div class="nt-grid">
                <div class="nt-fld"><label>{{ $t('Subject (EN)', 'العنوان (EN)') }}</label><input name="subject_en" value="{{ old('subject_en', $current['subject']['en'] ?? '') }}"></div>
                <div class="nt-fld"><label>{{ $t('Subject (AR)', 'العنوان (AR)') }}</label><input name="subject_ar" dir="rtl" value="{{ old('subject_ar', $current['subject']['ar'] ?? '') }}"></div>
                <div class="nt-fld"><label>{{ $t('Body (EN)', 'النص (EN)') }}</label><textarea name="body_en" rows="3">{{ old('body_en', $current['body']['en'] ?? '') }}</textarea></div>
                <div class="nt-fld"><label>{{ $t('Body (AR)', 'النص (AR)') }}</label><textarea name="body_ar" dir="rtl" rows="3">{{ old('body_ar', $current['body']['ar'] ?? '') }}</textarea></div>
            </div>

            <div class="nt-fld">
                <label>{{ $t('Channels', 'القنوات') }}</label>
                <div style="display:flex; gap:16px; flex-wrap:wrap;">
                    @foreach($allChannels as $ch)
                        <label style="display:flex; align-items:center; gap:6px; font-weight:500;">
                            <input type="checkbox" name="channels[]" value="{{ $ch }}" @checked(in_array($ch, old('channels', $current['channels']), true))> {{ $ch }}
                        </label>
                    @endforeach
                </div>
            </div>

            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $current['is_active']))>
                {{ $t('Use this custom version (otherwise the built-in default is used)', 'استخدم هذه النسخة المخصّصة (وإلا يُستخدم الافتراضي المدمج)') }}
            </label>

            <div style="margin-top:16px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-floppy-disk"></i> {{ $t('Save', 'حفظ') }}</button>
            </div>
        </div>
    </form>

@endsection
