@extends('panel.layouts.master')

@php
    $editing = (bool) $subService;
    $serviceTitle = app()->getLocale() === 'ar' ? ($service->title ?: $service->title_en) : ($service->title_en ?: $service->title);
@endphp

@section('title', $editing ? textByLanguage('تعديل خدمة فرعية', 'Edit sub-service') : textByLanguage('إضافة خدمة فرعية', 'Add sub-service'))
@section('page-title', $editing ? textByLanguage('تعديل خدمة فرعية', 'Edit sub-service') : textByLanguage('إضافة خدمة فرعية', 'Add sub-service'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? ($subService->name ?: $subService->name_en) : textByLanguage('خدمة فرعية جديدة', 'New sub-service')"
        :subtitle="$serviceTitle">
        <x-slot:actions>
            <a href="{{ route('panel.admin.service.sub.index', $service->id) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route('panel.admin.service.sub.update', [$service->id, $subService->id]) : route('panel.admin.service.sub.store', $service->id) }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات', 'Information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="name" :label="textByLanguage('الاسم (عربي)', 'Name (Arabic)')" :value="$subService?->name" required />
                <x-panel.field name="name_en" :label="textByLanguage('الاسم (إنجليزي)', 'Name (English)')" :value="$subService?->name_en" />
                <x-panel.field name="description" type="textarea" :label="textByLanguage('الوصف (عربي)', 'Description (Arabic)')" :value="$subService?->description" full />
                <x-panel.field name="description_en" type="textarea" :label="textByLanguage('الوصف (إنجليزي)', 'Description (English)')" :value="$subService?->description_en" full />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;" id="basePricesCard">
            <h3 class="p-card__title">{{ textByLanguage('الأسعار الأساسية (بالعدّاد)', 'Base prices (meter)') }}</h3>
            <p id="meterUnusedNote" style="display:none;margin:-6px 0 12px;font-size:.83rem;color:var(--p-text-muted);">
                <i class="bi bi-info-circle"></i>
                {{ textByLanguage(
                    'خدمة السفر تُسعَّر بخطوط ثابتة (مدينة ← مدينة) من شاشة «أسعار الخطوط» — القيم أدناه لا تؤثّر على ما يُعرض للراكب.',
                    'A Travel sub-service is priced by fixed city-to-city corridors on the “Fixed corridors” screen — the values below do not affect what the rider is quoted.'
                ) }}
            </p>
            <div class="p-form-grid">
                <x-panel.field name="openPrice" type="number" :label="textByLanguage('سعر الفتح', 'Open price')" :value="$subService?->openPrice ?? 0" required />
                <x-panel.field name="kmPrice" type="number" :label="textByLanguage('سعر الكيلومتر', 'Per km')" :value="$subService?->kmPrice ?? 0" required />
                <x-panel.field name="minutePrice" type="number" :label="textByLanguage('سعر الدقيقة', 'Per minute')" :value="$subService?->minutePrice ?? 0" required />
                <x-panel.field name="base_fare" type="number" :label="textByLanguage('الأجرة الأساسية', 'Base fare')" :value="$subService?->base_fare" />
                <x-panel.field name="sort_order" type="number" :label="textByLanguage('الترتيب', 'Sort order')" :value="$subService?->sort_order ?? 0" />
                <x-panel.field name="badge" :label="textByLanguage('شارة', 'Badge')" :value="$subService?->badge" />
                <x-panel.field name="icon" :label="textByLanguage('اسم الأيقونة', 'Icon name')" :value="$subService?->icon" />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الإعدادات', 'Settings') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="status" type="select" :label="textByLanguage('الحالة', 'Status')" :value="$subService?->status ?? 1"
                    :options="[1 => textByLanguage('مفعّلة', 'Active'), 0 => textByLanguage('معطّلة', 'Inactive')]" required />
                <div class="p-field">
                    <label>{{ textByLanguage('خيارات', 'Options') }}</label>
                    <label class="p-check"><input type="checkbox" name="is_travel" value="1" @checked(old('is_travel', $subService?->is_travel)) /> {{ textByLanguage('رحلة سفر', 'Travel trip') }}</label>
                </div>
                <div class="p-field p-field--full">
                    <label for="image">{{ textByLanguage('الصورة', 'Image') }}</label>
                    <input type="file" name="image" id="image" accept="image/*">
                    @error('image')<small class="p-field__error">{{ $message }}</small>@enderror
                    @if($editing && $subService->image)
                        <div style="margin-top:8px;"><img src="{{ asset('storage/'.$subService->image) }}" alt="" style="height:54px;border-radius:8px;"></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route('panel.admin.service.sub.index', $service->id) }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ $editing ? textByLanguage('حفظ', 'Save') : textByLanguage('إنشاء', 'Create') }}</button>
        </div>
    </form>

@endsection

@push('scripts')
<script>
(function () {
    var travel = document.querySelector('input[name="is_travel"]');
    var note = document.getElementById('meterUnusedNote');
    if (!travel || !note) return;

    // Meter prices are inert for a Travel sub-service; say so the moment the
    // box is ticked instead of letting someone tune numbers that do nothing.
    function sync() { note.style.display = travel.checked ? '' : 'none'; }

    travel.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
