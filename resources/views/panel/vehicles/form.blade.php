@extends('panel.layouts.master')

@php
    $editing = (bool) $vehicle;
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $driverChoices = ['' => textByLanguage('بدون سائق', 'No driver')] + $driverOptions;
@endphp

@section('title', $editing ? textByLanguage('تعديل مركبة', 'Edit vehicle') : textByLanguage('إضافة مركبة', 'Add vehicle'))
@section('page-title', $editing ? textByLanguage('تعديل مركبة', 'Edit vehicle') : textByLanguage('إضافة مركبة', 'Add vehicle'))

@section('content')

    <x-panel.page-toolbar
        :title="$editing ? trim($vehicle->vehicleBrand.' '.$vehicle->model) : textByLanguage('مركبة جديدة', 'New vehicle')"
        :subtitle="textByLanguage('بيانات المركبة', 'Vehicle details')">
        <x-slot:actions>
            <a href="{{ route($r('vehicle.index')) }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route($r('vehicle.update'), $vehicle->id) : route($r('vehicle.store')) }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('بيانات المركبة', 'Vehicle information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="vehicleBrand" :label="textByLanguage('الماركة', 'Brand')" :value="$vehicle?->vehicleBrand" :suggestions="$catalog['brands'] ?? []" required />
                <x-panel.field name="model" :label="textByLanguage('الطراز', 'Model')" :value="$vehicle?->model" :suggestions="$catalog['models'] ?? []" required />
                <x-panel.field name="plate" :label="textByLanguage('رقم اللوحة', 'Plate')" :value="$vehicle?->plate" required />
                <x-panel.field name="modelYear" type="select" :label="textByLanguage('سنة الصنع', 'Year')" :value="$vehicle?->modelYear"
                    :options="array_combine($catalog['years'] ?? [], $catalog['years'] ?? [])" required />
                @php $colorOptions = collect($catalog['colors'] ?? [])->mapWithKeys(fn ($c) => [$c => $c])->all(); @endphp
                @if($colorOptions !== [])
                    <x-panel.field name="color" type="select" :label="textByLanguage('اللون', 'Color')" :value="$vehicle?->color"
                        :options="$colorOptions" required />
                @else
                    <x-panel.field name="color" :label="textByLanguage('اللون', 'Color')" :value="$vehicle?->color" required />
                @endif
                @php $cityOptions = collect($catalog['cities'] ?? [])->mapWithKeys(fn ($c) => [$c => $c])->all(); @endphp
                @if($cityOptions !== [])
                    <x-panel.field name="city" type="select" :label="textByLanguage('المدينة', 'City')" :value="$vehicle?->city"
                        :options="$cityOptions" required />
                @else
                    <x-panel.field name="city" :label="textByLanguage('المدينة', 'City')" :value="$vehicle?->city" required />
                @endif
                <x-panel.field name="licenseNumber" :label="textByLanguage('رقم الترخيص', 'License number')" :value="$vehicle?->licenseNumber" />
                <x-panel.field name="seatsCount" type="number" :label="textByLanguage('عدد المقاعد', 'Seats')" :value="$vehicle?->seatsCount" />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الربط والإعدادات', 'Assignment & settings') }}</h3>
            <div class="p-form-grid">
                @if($isAdmin)
                    <x-panel.field name="officeId" type="select" :label="textByLanguage('المكتب', 'Office')" :value="$vehicle?->officeId" :options="$officeOptions" required />
                @endif
                <x-panel.field name="driverId" type="select" :label="textByLanguage('السائق', 'Driver')" :value="$vehicle?->driverId" :options="$driverChoices" />
                <div class="p-field">
                    <label>{{ textByLanguage('خيارات', 'Options') }}</label>
                    <label class="p-check"><input type="checkbox" name="fleet_car" value="1" @checked(old('fleet_car', $vehicle?->fleet_car)) /> {{ textByLanguage('سيارة أسطول (تابعة للمكتب)', 'Fleet car (office-owned)') }}</label>
                </div>
                <x-panel.field name="description" type="textarea" :label="textByLanguage('الوصف', 'Description')" :value="$vehicle?->description" full />
                <div class="p-field p-field--full">
                    <label for="photo">{{ textByLanguage('صورة المركبة', 'Vehicle photo') }}</label>
                    <input type="file" name="photo" id="photo" accept="image/*">
                    @error('photo')<small class="p-field__error">{{ $message }}</small>@enderror
                    @if($editing && $vehicle->photo)<div style="margin-top:8px;"><img src="{{ asset('storage/'.$vehicle->photo) }}" alt="" style="height:64px;border-radius:8px;"></div>@endif
                </div>
            </div>
        </div>

        <div class="p-form-actions">
            <a href="{{ route($r('vehicle.index')) }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ $editing ? textByLanguage('حفظ التغييرات', 'Save changes') : textByLanguage('إنشاء المركبة', 'Create vehicle') }}</button>
        </div>
    </form>

@endsection
