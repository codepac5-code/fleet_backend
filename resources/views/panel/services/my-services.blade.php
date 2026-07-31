@extends('panel.layouts.master')

@section('title', textByLanguage('خدماتي', 'My services'))
@section('page-title', textByLanguage('خدماتي', 'My services'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $offered = collect($prices)->filter(fn ($p) => (bool) ($p['is_enabled'] ?? true))->count();
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('خدمات مكتبي وأسعارها', 'My office services and prices')"
        :subtitle="textByLanguage('ما تختاره هنا هو ما يراه الراكب عند البحث عن مكتب', 'What you tick here is what riders see when they search for an office')" />

    @unless($assigned ?? true)
        {{-- An office with no main service has nothing of its own to price;
             showing it the whole country catalogue was how a city-taxi office
             ended up pricing another service's airport corridors. --}}
        <div class="p-flash p-flash--warn">
            <i class="bi bi-exclamation-triangle"></i>
            {{ textByLanguage(
                'مكتبك غير مُسنَد إلى أي خدمة رئيسية بعد — تواصل مع إدارة المنصّة لإسناده، وعندها تظهر خدماته الفرعية هنا للتسعير.',
                'Your office is not assigned to any main service yet — ask the platform to assign one, and its sub-services will appear here for pricing.'
            ) }}
        </div>
    @endunless

    @if(($services ?? collect())->contains(fn ($s) => (bool) ($s->travel_service ?? false)))
        {{-- A travel service is sold as fixed corridors: an open/km/minute price
             is not what this office charges, so send it where its prices live. --}}
        <div class="p-flash" style="background:rgba(49,40,115,.06);color:var(--p-primary);border:1px solid var(--p-border);">
            <i class="bi bi-signpost-split"></i>
            {{ textByLanguage(
                'من بين خدماتك خدمة سفر — تُسعَّر بالخطوط (مدينة ← مدينة) لا بالعدّاد. علّم هنا الفئات التي تقدّمها، وحدّد سعر كل خط من صفحة أسعار الخطوط.',
                'One of your services is a travel service — it is priced per corridor (city → city), not by the metre. Tick the classes you offer here, and set each corridor price on the Fixed corridors page.'
            ) }}
            @if(\Illuminate\Support\Facades\Route::has($r('pricing.corridors.index')))
                <a href="{{ route($r('pricing.corridors.index')) }}" class="p-btn p-btn--soft" style="margin-inline-start:auto;">
                    <i class="bi bi-sliders"></i> {{ textByLanguage('أسعار الخطوط', 'Fixed corridors') }}
                </a>
            @endif
        </div>
    @endif

    @if($offered === 0 && ($assigned ?? true))
        <div class="p-flash p-flash--err">
            <i class="bi bi-exclamation-triangle"></i>
            {{ textByLanguage(
                'مكتبك لا يقدّم أي خدمة حالياً — لن يظهر لأي راكب. علّم الخدمات التي تعمل عليها ثم احفظ.',
                'Your office offers no service at all — no rider can see it. Tick the services you work on, then save.'
            ) }}
        </div>
    @endif

    <div class="p-flash" style="background:rgba(49,40,115,.06);color:var(--p-primary);border:1px solid var(--p-border);">
        <i class="bi bi-info-circle"></i>
        {{ textByLanguage(
            'علّم «أقدّمها» لتظهر في هذه الخدمة، واترك السعر فارغاً لتعمل بالسعر الأساسي (الرقم الرمادي). إيقاف خدمة لا يحذف سعرك — يبقى محفوظاً لحين إعادة تفعيلها.',
            'Tick “I offer it” to appear for that service, and leave the price empty to work at the base price (the grey number). Turning a service off does not lose your price — it is kept for when you turn it back on.'
        ) }}
    </div>

    <form method="POST" action="{{ route($r('services.mine.update')) }}">
        @csrf
        @method('PUT')

        @forelse($catalog as $service)
            <div class="p-card" style="margin-bottom:18px;">
                <h3 class="p-card__title"><i class="bi bi-grid-1x2"></i> {{ $service['title'] }}</h3>

                @if(!empty($service['subServices']))
                    @php $isTravel = (bool) ($service['isTravel'] ?? false); @endphp
                    <x-panel.table :headers="array_values(array_filter([
                        textByLanguage('أقدّمها', 'I offer it'),
                        textByLanguage('الخدمة الفرعية', 'Sub-service'),
                        $isTravel ? null : textByLanguage('سعر الفتح', 'Open price') . ' (' . $currency . ')',
                        $isTravel ? null : textByLanguage('سعر الكيلومتر', 'Per km') . ' (' . $currency . ')',
                        $isTravel ? null : textByLanguage('سعر الدقيقة', 'Per minute') . ' (' . $currency . ')',
                    ], fn ($h) => $h !== null))">
                        @foreach($service['subServices'] as $sub)
                            @php $p = $prices[$sub['id']] ?? null; @endphp
                            @php $on = $p !== null ? (bool) ($p['is_enabled'] ?? true) : false; @endphp
                            <tr>
                                <td>
                                    <label class="p-check" style="margin:0;">
                                        <input type="checkbox" name="prices[{{ $sub['id'] }}][enabled]" value="1" @checked(old('prices.'.$sub['id'].'.enabled', $on))>
                                    </label>
                                </td>
                                <td><strong>{{ $sub['name'] }}</strong></td>
                                @unless($isTravel)
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
                                @endunless
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
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
        </div>
    </form>

@endsection
