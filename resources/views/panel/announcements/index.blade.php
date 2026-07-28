@extends('panel.layouts.master')

@section('title', textByLanguage('إرسال إشعار', 'Send announcement'))
@section('page-title', textByLanguage('إرسال إشعار', 'Send announcement'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
@endphp

@push('styles')
<style>
    .an-fld { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
    .an-fld label { font-size:.85rem; font-weight:600; }
    .an-fld input, .an-fld textarea, .an-fld select { width:100%; padding:10px 12px; border:1.5px solid var(--p-border); border-radius:var(--p-radius-sm); font-family:inherit; }
</style>
@endpush

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('إرسال إشعار جماعي', 'Broadcast a push notification')"
        :subtitle="textByLanguage('يُرسَل عبر إشعارات التطبيق لأجهزة هذه الدولة فقط', 'Sent via app push to this country\'s devices only')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    @if(shardIsAll())
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-octagon"></i>
            {{ textByLanguage('أنت في وضع «كل الدول» — اختر دولة محدّدة لإرسال إشعار (لمنع الإرسال لكل البلدان).', 'You are in "All countries" mode — pick a specific country to send (prevents blasting every country).') }}
        </div>
    @endif

    <form method="POST" action="{{ route($r('announcements.send')) }}" @if(shardIsAll()) onsubmit="return false;" style="opacity:.5;pointer-events:none;" @endif>
        @csrf
        <div class="p-card">
            <div class="an-fld">
                <label>{{ $t('Audience', 'الجمهور') }}</label>
                <select name="audience" required>
                    @if($isAdmin)<option value="riders">{{ $t('All riders', 'كل الركّاب') }} ({{ $riderCount }})</option>@endif
                    <option value="drivers">{{ $isAdmin ? $t('All drivers', 'كل السائقين') : $t('My drivers', 'سائقو مكتبي') }} ({{ $driverCount }})</option>
                </select>
            </div>
            <div class="an-fld">
                <label>{{ $t('Title', 'العنوان') }}</label>
                <input name="title" value="{{ old('title') }}" maxlength="120" required>
            </div>
            <div class="an-fld">
                <label>{{ $t('Message', 'الرسالة') }}</label>
                <textarea name="body" rows="4" maxlength="500" required>{{ old('body') }}</textarea>
            </div>
            <button type="submit" class="p-btn p-btn--primary" onclick="return confirm('{{ $t('Send this announcement?', 'إرسال هذا الإشعار؟') }}');">
                <i class="bi bi-send"></i> {{ $t('Send', 'إرسال') }}
            </button>
        </div>
    </form>

@endsection
