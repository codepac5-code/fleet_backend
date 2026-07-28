@extends('panel.layouts.master')

@php
    $editing = (bool) $driver;
    $r = fn ($name) => "panel.{$entity}.{$name}";
@endphp

@section('title', $editing ? textByLanguage('تعديل سائق', 'Edit driver') : textByLanguage('إضافة سائق', 'Add driver'))
@section('page-title', $editing ? textByLanguage('تعديل سائق', 'Edit driver') : textByLanguage('إضافة سائق', 'Add driver'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? trim($driver->firstName.' '.$driver->lastName) : textByLanguage('سائق جديد', 'New driver')"
        :subtitle="$editing ? textByLanguage('تحديث بيانات السائق', 'Update driver details') : textByLanguage('إضافة سائق جديد', 'Add a new driver')">
        <x-slot:actions>
            <a href="{{ route($r('driver.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST"
        action="{{ $editing ? route($r('driver.update'), $driver->id) : route($r('driver.store')) }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات الأساسية', 'Basic information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="firstName" :label="textByLanguage('الاسم الأول', 'First name')" :value="$driver?->firstName" required />
                <x-panel.field name="lastName" :label="textByLanguage('اسم العائلة', 'Last name')" :value="$driver?->lastName" required />
                <x-panel.field name="dialCode" :label="textByLanguage('رمز الدولة', 'Dial code')" :value="$driver?->dialCode" placeholder="+974" required />
                <x-panel.field name="phoneNumber" :label="textByLanguage('رقم الهاتف', 'Phone number')" :value="$driver?->phoneNumber" required />
                <x-panel.field name="password" type="password"
                    :label="$editing ? textByLanguage('كلمة مرور جديدة (اختياري)', 'New password (optional)') : textByLanguage('كلمة المرور', 'Password')"
                    :required="! $editing" />
                <x-panel.field name="gender" type="select" :label="textByLanguage('الجنس', 'Gender')" :value="$driver?->gender"
                    :options="['' => textByLanguage('غير محدد', 'Unspecified'), 'male' => textByLanguage('ذكر', 'Male'), 'female' => textByLanguage('أنثى', 'Female')]" />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('المكتب والموقع', 'Office & location') }}</h3>
            <div class="p-form-grid">
                @if($isAdmin)
                    <x-panel.field name="officeId" type="select" :label="textByLanguage('المكتب', 'Office')" :value="$driver?->officeId"
                        :options="$officeOptions" required />
                @endif
                <x-panel.field name="country" :label="textByLanguage('الدولة', 'Country')" :value="$driver?->country" required />
                <x-panel.field name="region" :label="textByLanguage('المنطقة', 'Region')" :value="$driver?->region" required />
                <x-panel.field name="city" :label="textByLanguage('المدينة', 'City')" :value="$driver?->city" required />
                <x-panel.field name="address" type="textarea" :label="textByLanguage('العنوان', 'Address')" :value="$driver?->address" full required />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الإعدادات', 'Settings') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="isActive" type="select" :label="textByLanguage('الحالة', 'Status')" :value="$driver?->isActive ?? 1"
                    :options="[1 => textByLanguage('مفعّل', 'Active'), 0 => textByLanguage('معطّل', 'Inactive')]" required />
                <div class="p-field">
                    <label>{{ textByLanguage('خيارات', 'Options') }}</label>
                    <label class="p-check"><input type="checkbox" name="car_owner" value="1" @checked(old('car_owner', $driver?->car_owner)) /> {{ textByLanguage('يملك مركبة', 'Car owner') }}</label>
                    <label class="p-check"><input type="checkbox" name="free_driver" value="1" @checked(old('free_driver', $driver?->free_driver)) /> {{ textByLanguage('سائق حر', 'Free driver') }}</label>
                </div>
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route($r('driver.index')) }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary">
                <i class="bi bi-check-lg"></i>
                {{ $editing ? textByLanguage('حفظ التغييرات', 'Save changes') : textByLanguage('إنشاء السائق', 'Create driver') }}
            </button>
        </div>
    </form>

@endsection
