@extends('panel.layouts.master')

@php
    $editing = (bool) $employee;
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $jobName = $employee?->employeeJobName_ar ?: $employee?->employeeJobName_en;
    $jobDesc = $employee?->job_description_ar ?: $employee?->job_description_en;
@endphp

@section('title', $editing ? textByLanguage('تعديل موظف', 'Edit employee') : textByLanguage('إضافة موظف', 'Add employee'))
@section('page-title', $editing ? textByLanguage('تعديل موظف', 'Edit employee') : textByLanguage('إضافة موظف', 'Add employee'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? trim($employee->firstName.' '.$employee->lastName) : textByLanguage('موظف جديد', 'New employee')"
        :subtitle="$editing ? textByLanguage('تحديث بيانات الموظف', 'Update employee details') : textByLanguage('إضافة عضو جديد للفريق', 'Add a new team member')">
        <x-slot:actions>
            <a href="{{ route($r('employee.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST"
        action="{{ $editing ? route($r('employee.update'), $employee->id) : route($r('employee.store')) }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات الأساسية', 'Basic information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="firstName" :label="textByLanguage('الاسم الأول', 'First name')" :value="$employee?->firstName" required />
                <x-panel.field name="lastName" :label="textByLanguage('اسم العائلة', 'Last name')" :value="$employee?->lastName" required />
                <x-panel.field name="email" type="email" :label="textByLanguage('البريد الإلكتروني', 'Email')" :value="$employee?->email" required />
                <x-panel.field name="phoneNumber" :label="textByLanguage('رقم الهاتف', 'Phone number')" :value="$employee?->phoneNumber" required />
                <x-panel.field name="password" type="password"
                    :label="$editing ? textByLanguage('كلمة مرور جديدة (اختياري)', 'New password (optional)') : textByLanguage('كلمة المرور', 'Password')"
                    :required="! $editing" />
                <x-panel.field name="gender" type="select" :label="textByLanguage('الجنس', 'Gender')" :value="$employee?->gender"
                    :options="['male' => textByLanguage('ذكر', 'Male'), 'female' => textByLanguage('أنثى', 'Female')]" required />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الوظيفة', 'Role') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="jobName" :label="textByLanguage('المسمى الوظيفي', 'Job title')" :value="$jobName" required />
                <x-panel.field name="role" type="select" :label="textByLanguage('الدور', 'Role')" :value="$employee?->role"
                    :options="\App\Http\Services\Panel\Employees\Logic\EmployeeRole::options()" required />
                <div class="p-field p-field--full" style="background:var(--p-bg);border-radius:var(--p-radius-sm);padding:12px 14px;">
                    @foreach(\App\Http\Services\Panel\Employees\Logic\EmployeeRole::ALL as $roleKey)
                        <p style="margin:0 0 6px;font-size:.82rem;">
                            <strong>{{ \App\Http\Services\Panel\Employees\Logic\EmployeeRole::label($roleKey) }}</strong>
                            <span style="color:var(--p-text-muted);"> — {{ \App\Http\Services\Panel\Employees\Logic\EmployeeRole::description($roleKey) }}</span>
                        </p>
                    @endforeach
                    <p style="margin:8px 0 0;font-size:.78rem;color:var(--p-text-muted);">
                        <i class="bi bi-info-circle"></i>
                        {{ textByLanguage('تُطبَّق صلاحيات الدور فور الحفظ، ويمكن تعديلها لاحقاً لكل موظف من شاشة الصلاحيات.', 'The role grants its permissions on save; you can fine-tune any employee afterwards from the permissions screen.') }}
                    </p>
                </div>
                @if($isAdmin)
                    <x-panel.field name="officeId" type="select" :label="textByLanguage('المكتب', 'Office')" :value="$employee?->officeId"
                        :options="$officeOptions" required />
                @endif
                <x-panel.field name="jobDescription" type="textarea" :label="textByLanguage('وصف الوظيفة', 'Job description')" :value="$jobDesc" full />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الموقع والحالة', 'Location & status') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="country" :label="textByLanguage('الدولة', 'Country')" :value="$employee?->country" required />
                <x-panel.field name="region" :label="textByLanguage('المنطقة', 'Region')" :value="$employee?->region" required />
                <x-panel.field name="city" :label="textByLanguage('المدينة', 'City')" :value="$employee?->city" required />
                <x-panel.field name="isActive" type="select" :label="textByLanguage('الحالة', 'Status')" :value="$employee?->isActive ?? 1"
                    :options="[1 => textByLanguage('مفعّل', 'Active'), 0 => textByLanguage('معطّل', 'Inactive')]" required />
                <x-panel.field name="address" type="textarea" :label="textByLanguage('العنوان', 'Address')" :value="$employee?->address" full required />
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route($r('employee.index')) }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary">
                <i class="bi bi-check-lg"></i>
                {{ $editing ? textByLanguage('حفظ التغييرات', 'Save changes') : textByLanguage('إنشاء الموظف', 'Create employee') }}
            </button>
        </div>
    </form>

@endsection
