@extends('panel.layouts.master')

@php $editing = (bool) $office; @endphp

@section('title', $editing ? textByLanguage('تعديل مكتب', 'Edit office') : textByLanguage('إضافة مكتب', 'Add office'))
@section('page-title', $editing ? textByLanguage('تعديل مكتب', 'Edit office') : textByLanguage('إضافة مكتب', 'Add office'))

@section('content')

    <style>
        .p-field-wide { grid-column: 1 / -1; display: flex; flex-direction: column; gap: 8px; }
        .p-field-label { font-weight: 600; }
        .svc-picker { display: flex; flex-wrap: wrap; gap: 10px; }
        .svc-chip { display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border: 1px solid var(--p-border); border-radius: 999px; cursor: pointer; transition: background .15s, border-color .15s; }
        .svc-chip:hover { border-color: var(--p-primary); }
        .svc-chip.is-on { background: rgba(49, 40, 115, .07); border-color: var(--p-primary); }
        .svc-chip em { font-style: normal; font-size: 12px; opacity: .6; }
    </style>

    <x-panel.page-toolbar
        :title="$editing ? $office->officeName : textByLanguage('مكتب جديد', 'New office')"
        :subtitle="$editing ? textByLanguage('تحديث بيانات المكتب', 'Update office details') : textByLanguage('إنشاء حساب مكتب جديد', 'Create a new office account')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.office.index') }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <form method="POST"
        action="{{ $editing ? route('panel.admin.office.update', $office->id) : route('panel.admin.office.store') }}">
        @csrf
        @if($editing)@method('PUT')@endif

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('البيانات الأساسية', 'Basic information') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="officeName" :label="textByLanguage('اسم المكتب', 'Office name')"
                    :value="$office?->officeName" required />
                {{-- The main services the office is set up under — one company
                     routinely runs the city meter service AND sells airport
                     corridors. Everything it may price hangs off these. --}}
                @php $picked = collect(old('service_ids', $assignedServiceIds ?? []))->map(fn ($id) => (int) $id); @endphp
                <div class="p-field-wide">
                    <span class="p-field-label">{{ textByLanguage('الخدمات الرئيسية', 'Main services') }}</span>
                    {{-- Unticking every box sends no checkbox at all; this keeps
                         the field present so "none" is heard as none. --}}
                    <input type="hidden" name="service_ids[]" value="">
                    <div class="svc-picker">
                        @forelse(($services ?? collect()) as $svc)
                            <label class="svc-chip @if($picked->contains((int) $svc->id)) is-on @endif">
                                <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                                    @checked($picked->contains((int) $svc->id))>
                                <span>{{ app()->getLocale() === 'ar' ? ($svc->title ?: $svc->title_en) : ($svc->title_en ?: $svc->title) }}</span>
                                <em>@if($svc->travel_service){{ textByLanguage('سفر (خطوط)', 'travel (corridors)') }}@else{{ textByLanguage('عدّاد', 'meter') }}@endif</em>
                            </label>
                        @empty
                            <span class="p-muted">{{ textByLanguage('لا توجد خدمات في هذه الدولة بعد', 'No services in this country yet') }}</span>
                        @endforelse
                    </div>
                    <small class="p-muted">{{ textByLanguage('اختر خدمة أو أكثر — يستطيع المكتب تسعير الفئات التابعة لها فقط.', 'Pick one or more — the office may price only the classes beneath them.') }}</small>
                    <script>
                        document.querySelectorAll('.svc-chip input').forEach(function (box) {
                            box.addEventListener('change', function () {
                                box.closest('.svc-chip').classList.toggle('is-on', box.checked);
                            });
                        });
                    </script>
                </div>
                <x-panel.field name="email" type="email" :label="textByLanguage('البريد الإلكتروني', 'Email')"
                    :value="$office?->email" required />
                <x-panel.field name="contactNumber" :label="textByLanguage('رقم التواصل', 'Contact number')"
                    :value="$office?->contactNumber" />
                <x-panel.field name="password" type="password"
                    :label="$editing ? textByLanguage('كلمة مرور جديدة (اختياري)', 'New password (optional)') : textByLanguage('كلمة المرور', 'Password')"
                    :required="! $editing" />
            </div>
        </div>

        {{-- The platform's cut, agreed with THIS office. Left empty it tracks
             the platform default, so an office nobody has negotiated with keeps
             following the rate rather than freezing at today's number. --}}
        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('العمولة', 'Commission') }}</h3>
            <div class="p-form-grid">
                <div class="p-field">
                    <label for="fcr">{{ textByLanguage('عمولة فليت من هذا المكتب', "FleetOS's cut from this office") }}</label>
                    <div class="p-pct">
                        <input type="number" step="0.01" min="0" max="100" id="fcr" name="fleet_commission_rate"
                            value="{{ old('fleet_commission_rate', $office?->fleet_commission_rate) }}"
                            placeholder="{{ rtrim(rtrim(number_format($defaultFleetRate ?? 5, 2), '0'), '.') }}">
                        <span>%</span>
                    </div>
                    @error('fleet_commission_rate')<small class="p-field__error">{{ $message }}</small>@enderror
                    <small class="p-muted">{{ textByLanguage('اتركه فارغاً ليتبع الافتراضي. الباقي للمكتب وسائقه، والمكتب هو من يقسّمه بينهما.', "Leave empty to follow the default. The rest goes to the office and its driver, and the office decides how to divide it.") }}</small>
                </div>
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الموقع', 'Location') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="country" :label="textByLanguage('الدولة', 'Country')" :value="$office?->country" />
                <x-panel.field name="region" :label="textByLanguage('المنطقة', 'Region')" :value="$office?->region" />
                <x-panel.field name="city" :label="textByLanguage('المدينة', 'City')" :value="$office?->city" />
                <x-panel.field name="address" type="textarea" :label="textByLanguage('العنوان', 'Address')"
                    :value="$office?->address" full />
                <x-panel.field name="lat" type="number" :label="textByLanguage('خط العرض', 'Latitude')" :value="$office?->lat" />
                <x-panel.field name="lng" type="number" :label="textByLanguage('خط الطول', 'Longitude')" :value="$office?->lng" />
            </div>
        </div>

        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('الإعدادات', 'Settings') }}</h3>
            <div class="p-form-grid">
                <x-panel.field name="limitOrders" type="number" :label="textByLanguage('حد الطلبات (0 = غير محدود)', 'Order limit (0 = unlimited)')"
                    :value="$office?->limitOrders ?? 0" />
                <x-panel.field name="status" type="select" :label="textByLanguage('الحالة', 'Status')"
                    :value="$office?->status ?? 1"
                    :options="[1 => textByLanguage('مفعّل', 'Active'), 0 => textByLanguage('معطّل', 'Inactive')]" required />
                <div class="p-field">
                    <label>{{ textByLanguage('حالة المكتب في التطبيق', 'Rider-app status') }}</label>
                    <label class="p-check"><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $office?->is_verified)) /> {{ textByLanguage('مكتب موثّق', 'Verified office') }}</label>
                    <label class="p-check"><input type="checkbox" name="is_monitored" value="1" @checked(old('is_monitored', $office?->is_monitored)) /> {{ textByLanguage('تحت المراقبة', 'Monitored') }}</label>
                </div>
            </div>
        </div>

        @php
            $whDays = [
                'sat' => ['السبت', 'Saturday'], 'sun' => ['الأحد', 'Sunday'], 'mon' => ['الاثنين', 'Monday'],
                'tue' => ['الثلاثاء', 'Tuesday'], 'wed' => ['الأربعاء', 'Wednesday'], 'thu' => ['الخميس', 'Thursday'], 'fri' => ['الجمعة', 'Friday'],
            ];
            $wh = old('working_hours', $office?->working_hours ?? []);
        @endphp
        <div class="p-card" style="margin-bottom:18px;">
            <h3 class="p-card__title">{{ textByLanguage('ساعات العمل', 'Working hours') }}</h3>
            <div class="p-hours">
                @foreach($whDays as $key => $label)
                    @php $row = $wh[$key] ?? []; $closed = ! empty($row['closed']); @endphp
                    <div class="p-hours__row" data-hrow>
                        <span class="p-hours__day">{{ textByLanguage($label[0], $label[1]) }}</span>
                        <label class="p-check p-hours__closed"><input type="checkbox" name="working_hours[{{ $key }}][closed]" value="1" @checked($closed) data-hclosed> {{ textByLanguage('مغلق', 'Closed') }}</label>
                        <input type="time" class="p-hours__t" name="working_hours[{{ $key }}][open]" value="{{ $row['open'] ?? '09:00' }}" @disabled($closed) data-htime>
                        <span class="p-hours__sep">—</span>
                        <input type="time" class="p-hours__t" name="working_hours[{{ $key }}][close]" value="{{ $row['close'] ?? '22:00' }}" @disabled($closed) data-htime>
                    </div>
                @endforeach
            </div>
            <p class="p-plan-note"><i class="bi bi-clock"></i> {{ textByLanguage('تظهر في بطاقة المكتب داخل تطبيق الراكب.', 'Shown on the office card in the rider app.') }}</p>
        </div>

        <script>
            document.querySelectorAll('[data-hrow]').forEach(function (row) {
                var cb = row.querySelector('[data-hclosed]');
                if (!cb) return;
                cb.addEventListener('change', function () {
                    row.querySelectorAll('[data-htime]').forEach(function (t) { t.disabled = cb.checked; });
                });
            });
        </script>

        <div class="p-form-actions">
            <a href="{{ route('panel.admin.office.index') }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary">
                <i class="bi bi-check-lg"></i>
                {{ $editing ? textByLanguage('حفظ التغييرات', 'Save changes') : textByLanguage('إنشاء المكتب', 'Create office') }}
            </button>
        </div>
    </form>

@endsection
