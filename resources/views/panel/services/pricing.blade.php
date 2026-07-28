@extends('panel.layouts.master')

@section('title', textByLanguage('تسعير المكتب', 'Office pricing'))
@section('page-title', textByLanguage('تسعير المكتب', 'Office pricing'))

@section('content')

    <x-panel.page-toolbar
        :title="$office->officeName"
        :subtitle="textByLanguage('تحديد أسعار الخدمات الخاصة بهذا المكتب', 'Set service prices for this office')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.office.index') }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-flash" style="background:rgba(49,40,115,.06);color:var(--p-primary);border:1px solid var(--p-border);">
        <i class="bi bi-info-circle"></i>
        {{ textByLanguage('اترك الحقل فارغاً لاستخدام السعر الأساسي للخدمة. الأرقام الرمادية هي الأسعار الأساسية.', 'Leave a field empty to use the service base price. Grey numbers are the base prices.') }}
    </div>

    <form method="POST" action="{{ route('panel.admin.office.pricing.update', $office->id) }}">
        @csrf
        @method('PUT')

        @forelse($catalog as $service)
            <div class="p-card" style="margin-bottom:18px;">
                <h3 class="p-card__title"><i class="bi bi-grid-1x2"></i> {{ $service['title'] }}</h3>

                @if(!empty($service['subServices']))
                    <x-panel.table :headers="[
                        textByLanguage('الخدمة الفرعية', 'Sub-service'),
                        textByLanguage('سعر الفتح', 'Open price'),
                        textByLanguage('سعر الكيلومتر', 'Per km'),
                        textByLanguage('سعر الدقيقة', 'Per minute'),
                    ]">
                        @foreach($service['subServices'] as $sub)
                            @php $p = $prices[$sub['id']] ?? null; @endphp
                            <tr>
                                <td><strong>{{ $sub['name'] }}</strong></td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="p-price-input" name="prices[{{ $sub['id'] }}][openPrice]"
                                        value="{{ old('prices.'.$sub['id'].'.openPrice', $p['openPrice'] ?? '') }}"
                                        placeholder="{{ number_format($sub['openPrice'], 2) }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="p-price-input" name="prices[{{ $sub['id'] }}][kmPrice]"
                                        value="{{ old('prices.'.$sub['id'].'.kmPrice', $p['kmPrice'] ?? '') }}"
                                        placeholder="{{ number_format($sub['kmPrice'], 2) }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="p-price-input" name="prices[{{ $sub['id'] }}][minutePrice]"
                                        value="{{ old('prices.'.$sub['id'].'.minutePrice', $p['minutePrice'] ?? '') }}"
                                        placeholder="{{ number_format($sub['minutePrice'], 2) }}">
                                </td>
                            </tr>
                        @endforeach
                    </x-panel.table>
                @else
                    <p class="p-empty" style="padding:14px;"><i class="bi bi-inbox"></i> {{ textByLanguage('لا توجد خدمات فرعية مفعّلة', 'No active sub-services') }}</p>
                @endif
            </div>
        @empty
            <div class="p-card"><p class="p-empty"><i class="bi bi-grid-1x2"></i> {{ textByLanguage('لا توجد خدمات', 'No services') }}</p></div>
        @endforelse

        <div class="p-form-actions">
            <a href="{{ route('panel.admin.office.index') }}" class="p-btn p-btn--ghost">{{ textByLanguage('إلغاء', 'Cancel') }}</a>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ الأسعار', 'Save prices') }}</button>
        </div>
    </form>

@endsection
