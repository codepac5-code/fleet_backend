@extends('panel.layouts.master')

@php $editing = (bool) $service; @endphp

@section('title', $editing ? textByLanguage('تعديل خدمة', 'Edit service') : textByLanguage('إضافة خدمة', 'Add service'))
@section('page-title', $editing ? textByLanguage('تعديل خدمة', 'Edit service') : textByLanguage('إضافة خدمة', 'Add service'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? ($service->title ?: $service->title_en) : textByLanguage('خدمة جديدة', 'New service')"
        :subtitle="textByLanguage('بيانات الخدمة', 'Service details')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.service.index') }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route('panel.admin.service.update', $service->id) : route('panel.admin.service.store') }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات', 'Information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="title" :label="textByLanguage('الاسم (عربي)', 'Name (Arabic)')" :value="$service?->title" required />
                <x-panel.field name="title_en" :label="textByLanguage('الاسم (إنجليزي)', 'Name (English)')" :value="$service?->title_en" />
                <x-panel.field name="description" type="textarea" :label="textByLanguage('الوصف (عربي)', 'Description (Arabic)')" :value="$service?->description" full />
                <x-panel.field name="description_en" type="textarea" :label="textByLanguage('الوصف (إنجليزي)', 'Description (English)')" :value="$service?->description_en" full />
                <x-panel.field name="status" type="select" :label="textByLanguage('الحالة', 'Status')" :value="$service?->status ?? 1"
                    :options="[1 => textByLanguage('مفعّلة', 'Active'), 0 => textByLanguage('معطّلة', 'Inactive')]" required />
                <x-panel.field name="sort_order" type="number" :label="textByLanguage('الترتيب', 'Sort order')" :value="$service?->sort_order ?? 0" />
                <x-panel.field name="badge" :label="textByLanguage('شارة (مثل جديد/الأكثر رواجاً)', 'Badge (e.g. New/Popular)')" :value="$service?->badge" />
                <x-panel.field name="icon" :label="textByLanguage('اسم الأيقونة', 'Icon name')" :value="$service?->icon" />
                <div class="p-field">
                    <label>{{ textByLanguage('خيارات', 'Options') }}</label>
                    <label class="p-check"><input type="checkbox" name="travel_service" value="1" @checked(old('travel_service', $service?->travel_service)) /> {{ textByLanguage('خدمة سفر', 'Travel service') }}</label>
                </div>
                <div class="p-field p-field--full">
                    <label for="image">{{ textByLanguage('الصورة', 'Image') }}</label>
                    <input type="file" name="image" id="image" accept="image/*">
                    @error('image')<small class="p-field__error">{{ $message }}</small>@enderror
                    @if($editing && $service->image)
                        <div style="margin-top:8px;"><img src="{{ asset('storage/'.$service->image) }}" alt="" style="height:54px;border-radius:8px;"></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route('panel.admin.service.index') }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ $editing ? textByLanguage('حفظ', 'Save') : textByLanguage('إنشاء', 'Create') }}</button>
        </div>
    </form>

@endsection
