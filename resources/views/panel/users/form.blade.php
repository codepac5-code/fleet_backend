@extends('panel.layouts.master')

@php
    $editing = (bool) $record;
    $r = fn ($name) => "panel.{$entity}.{$name}";
@endphp

@section('title', $editing ? textByLanguage('تعديل مستخدم', 'Edit user') : textByLanguage('إضافة مستخدم', 'Add user'))
@section('page-title', $editing ? textByLanguage('تعديل مستخدم', 'Edit user') : textByLanguage('إضافة مستخدم', 'Add user'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? trim($record->firstName.' '.$record->lastName) : textByLanguage('مستخدم جديد', 'New user')"
        :subtitle="$editing ? textByLanguage('تحديث بيانات المستخدم', 'Update user details') : textByLanguage('إضافة عميل جديد', 'Add a new customer')">
        <x-slot:actions>
            <a href="{{ route($r('user.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST"
        action="{{ $editing ? route($r('user.update'), $record->id) : route($r('user.store')) }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات الأساسية', 'Basic information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="firstName" :label="textByLanguage('الاسم الأول', 'First name')" :value="$record?->firstName" required />
                <x-panel.field name="lastName" :label="textByLanguage('اسم العائلة', 'Last name')" :value="$record?->lastName" required />
                <x-panel.field name="dialCode" :label="textByLanguage('رمز الدولة', 'Dial code')" :value="$record?->dialCode" placeholder="+974" required />
                <x-panel.field name="phoneNumber" :label="textByLanguage('رقم الهاتف', 'Phone number')" :value="$record?->phoneNumber" required />
                <x-panel.field name="password" type="password"
                    :label="$editing ? textByLanguage('كلمة مرور جديدة (اختياري)', 'New password (optional)') : textByLanguage('كلمة المرور', 'Password')"
                    :required="! $editing" />
                <x-panel.field name="gender" type="select" :label="textByLanguage('الجنس', 'Gender')" :value="$record?->gender"
                    :options="['' => textByLanguage('غير محدد', 'Unspecified'), 'male' => textByLanguage('ذكر', 'Male'), 'female' => textByLanguage('أنثى', 'Female')]" />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('إعدادات إضافية', 'Additional settings') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="referralCode" :label="textByLanguage('رمز الإحالة', 'Referral code')" :value="$record?->referralCode" />
                <x-panel.field name="isActive" type="select" :label="textByLanguage('الحالة', 'Status')" :value="$record?->isActive ?? 1"
                    :options="[1 => textByLanguage('مفعّل', 'Active'), 0 => textByLanguage('معطّل', 'Inactive')]" required />
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route($r('user.index')) }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary">
                <i class="bi bi-check-lg"></i>
                {{ $editing ? textByLanguage('حفظ التغييرات', 'Save changes') : textByLanguage('إنشاء المستخدم', 'Create user') }}
            </button>
        </div>
    </form>

@endsection
