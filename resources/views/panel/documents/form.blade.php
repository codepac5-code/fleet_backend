@extends('panel.layouts.master')

@php $editing = (bool) $document; @endphp

@section('title', $editing ? textByLanguage('تعديل نوع مستند', 'Edit document type') : textByLanguage('إضافة نوع مستند', 'Add document type'))
@section('page-title', $editing ? textByLanguage('تعديل نوع مستند', 'Edit document type') : textByLanguage('إضافة نوع مستند', 'Add document type'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? $document->name : textByLanguage('نوع مستند جديد', 'New document type')"
        :subtitle="textByLanguage('تعريف مستند مطلوب من السائقين', 'Define a document required from drivers')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.document.index') }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST" action="{{ $editing ? route('panel.admin.document.update', $document->id) : route('panel.admin.document.store') }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات', 'Information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="name" :label="textByLanguage('اسم المستند', 'Document name')" :value="$document?->name" placeholder="{{ textByLanguage('رخصة قيادة، هوية…', 'Driving license, ID…') }}" required full />
                <div class="p-field">
                    <label>{{ textByLanguage('الخيارات', 'Options') }}</label>
                    <label class="p-check"><input type="checkbox" name="is_required" value="1" @checked(old('is_required', $document?->is_required ?? false)) /> {{ textByLanguage('إلزامي', 'Required') }}</label>
                    <label class="p-check"><input type="checkbox" name="status" value="1" @checked(old('status', $document?->status ?? true)) /> {{ textByLanguage('مفعّل', 'Active') }}</label>
                </div>
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route('panel.admin.document.index') }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ $editing ? textByLanguage('حفظ', 'Save') : textByLanguage('إنشاء', 'Create') }}</button>
        </div>
    </form>

@endsection
