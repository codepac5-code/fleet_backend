@extends('panel.layouts.master')

@section('title', textByLanguage('التعرفات', 'Tariffs'))
@section('page-title', textByLanguage('التعرفات', 'Tariffs'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $money = fn ($m) => number_format(((int) $m) / 100, 2);
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('تعرفات مكتبي', 'My tariffs')"
        :subtitle="textByLanguage('سعّر كل فئة خدمة (عدّاد أو ثابت)', 'Price each service class (meter or fixed)')" />

    <x-panel.card :title="textByLanguage('إضافة / تعديل تعرفة', 'Add / edit tariff')">
        <form method="POST" action="{{ route($r('tariffs.save')) }}" class="p-form-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem;align-items:end;">
            @csrf
            <label>{{ textByLanguage('فئة الخدمة', 'Service class') }}
                <select name="service_class" class="p-input" required>
                    @foreach($serviceClasses as $sc)
                        <option value="{{ $sc }}">{{ ucfirst($sc) }}</option>
                    @endforeach
                </select>
            </label>
            <label>{{ textByLanguage('النمط', 'Style') }}
                <select name="pricing_style" class="p-input">
                    <option value="meter">{{ textByLanguage('عدّاد', 'Meter') }}</option>
                    <option value="fixed">{{ textByLanguage('ثابت', 'Fixed') }}</option>
                </select>
            </label>
            {{-- Amounts are entered in WHOLE CURRENCY (e.g. 8000.00), not minor
                 units. The labels used to read just "Base" / "Per km" while the
                 fields were named `*_minor` and stored as hundredths, so an
                 office that meant 8000 got 80.00 — every price 100x too small.
                 The unit is now stated on every field and converted on save. --}}
            <label>{{ textByLanguage('الأساس', 'Base') }} <small>({{ $currency }})</small>
                <input type="number" name="base_amount" min="0" step="0.01" value="0" class="p-input"></label>
            <label>{{ textByLanguage('لكل كم', 'Per km') }} <small>({{ $currency }})</small>
                <input type="number" name="per_km_amount" min="0" step="0.01" value="0" class="p-input"></label>
            <label>{{ textByLanguage('لكل دقيقة', 'Per min') }} <small>({{ $currency }})</small>
                <input type="number" name="per_minute_amount" min="0" step="0.01" value="0" class="p-input"></label>
            <label>{{ textByLanguage('الحدّ الأدنى', 'Minimum') }} <small>({{ $currency }})</small>
                <input type="number" name="minimum_amount" min="0" step="0.01" value="0" class="p-input"></label>
            <label>{{ textByLanguage('السعر الثابت', 'Fixed') }} <small>({{ $currency }})</small>
                <input type="number" name="fixed_amount" min="0" step="0.01" value="0" class="p-input"></label>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-save"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
        </form>
        <p class="p-cell-sub" style="margin-top:.5rem;">{{ textByLanguage('القيم بالوحدة الصغرى (سنت). الحفظ يستبدل تعرفة الفئة نفسها.', 'Values in minor units (cents). Saving upserts the same class.') }}</p>
    </x-panel.card>

    <div class="p-card">
        @if(count($tariffs))
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('الفئة', 'Class'),
                textByLanguage('النمط', 'Style'),
                textByLanguage('الأساس', 'Base'),
                textByLanguage('كم/دقيقة', 'Km/Min'),
                textByLanguage('الأدنى', 'Min'),
                textByLanguage('الثابت', 'Fixed'),
                textByLanguage('الحالة', 'Status'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($tariffs as $t)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($t) ?: '—' }}</x-panel.badge></td>@endif
                        <td><strong>{{ ucfirst($t['service_class']) }}</strong></td>
                        <td><x-panel.badge :tone="$t['pricing_style'] === 'fixed' ? 'warning' : 'primary'">{{ ucfirst($t['pricing_style']) }}</x-panel.badge></td>
                        <td>{{ $money($t['base_minor']) }}</td>
                        <td>{{ $money($t['per_km_minor']) }} / {{ $money($t['per_minute_minor']) }}</td>
                        <td>{{ $money($t['minimum_minor']) }}</td>
                        <td>{{ $money($t['fixed_minor']) }}</td>
                        <td><x-panel.badge :tone="$t['is_active'] ? 'success' : 'danger'">{{ $t['is_active'] ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Off') }}</x-panel.badge></td>
                        <td>
                            <form method="POST" action="{{ route($r('tariffs.delete'), $t['service_class']) }}"
                                onsubmit="return confirm('{{ textByLanguage('حذف تعرفة هذه الفئة؟', 'Delete this class tariff?') }}');">
                                @csrf @method('DELETE')
                                @if(shardOf($t))<input type="hidden" name="country" value="{{ shardOf($t) }}">@endif
                                <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-cash-coin"></i> {{ textByLanguage('لا توجد تعرفات بعد', 'No tariffs yet') }}</p>
        @endif
    </div>

@endsection
