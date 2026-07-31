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

    @php
        $snap = $snapshot ?? null;
        $cur = $snap['currency'] ?? '';
        $m = fn ($minor) => number_format(((int) $minor) / 100, 2) . ' ' . $cur;
        $sub = $snap['subscription'] ?? ['mode' => 'commission', 'row' => null];
        $co = $snap['corridors'] ?? ['count' => 0, 'rows' => []];
    @endphp

    @if($snap)
        <div class="p-grid p-grid--4" style="margin-bottom:18px;">
            <x-panel.stat :label="textByLanguage('أرباح المكتب (الشهر)', 'Office earnings (month)')"
                          :value="$m($snap['earnings']['monthMinor'])" icon="bi-graph-up-arrow" />
            <x-panel.stat :label="textByLanguage('إجمالي الأرباح', 'Lifetime earnings')"
                          :value="$m($snap['earnings']['lifetimeMinor'])" icon="bi-cash-stack" />
            <x-panel.stat :label="textByLanguage('رصيد المحفظة', 'Wallet balance')"
                          :value="$m($snap['finance']['walletMinor'])" icon="bi-wallet2" />
            <x-panel.stat :label="textByLanguage('مستحقّات على المكتب', 'Owed to the fleet')"
                          :value="number_format($snap['finance']['fleetDues'], 2) . ' ' . $cur"
                          icon="bi-exclamation-diamond" :variant="$snap['finance']['fleetDues'] > 0 ? 'danger' : null" />
        </div>

        <div class="p-grid p-grid--2" style="align-items:start;margin-bottom:18px;">
            <div class="p-card">
                <div class="p-card__head">
                    <h3 class="p-card__title" style="margin:0;"><i class="bi bi-award"></i> {{ textByLanguage('الاشتراك', 'Subscription') }}</h3>
                    @if(\Illuminate\Support\Facades\Route::has('panel.admin.office.subscription.show'))
                        <a href="{{ route('panel.admin.office.subscription.show', $office->id) }}" class="p-btn p-btn--soft"><i class="bi bi-box-arrow-up-right"></i> {{ textByLanguage('التفاصيل', 'Details') }}</a>
                    @endif
                </div>

                @if($sub['mode'] === 'commission')
                    {{-- A commission country never bills a subscription; saying
                         "no subscription" without that context reads as a fault. --}}
                    <p style="margin:0;font-size:.88rem;">
                        <x-panel.badge tone="primary">{{ textByLanguage('نظام عمولة', 'Commission region') }}</x-panel.badge>
                    </p>
                    <p class="p-cell-sub" style="margin-top:8px;">
                        {{ textByLanguage('هذه الدولة لا تُحصّل اشتراكات — الدخل يأتي كعمولة من كل رحلة مكتملة.', 'This country charges no subscription — income is a commission on each completed ride.') }}
                    </p>
                @elseif($sub['row'])
                    <p style="margin:0;font-size:.88rem;">
                        <x-panel.badge :tone="in_array($sub['row']->status, ['active', 'trialing']) ? 'success' : 'danger'">{{ $sub['row']->status }}</x-panel.badge>
                        <strong style="margin-inline-start:8px;">{{ $sub['row']->plan_key }}</strong>
                    </p>
                    <p class="p-cell-sub" style="margin-top:8px;">
                        @if($sub['row']->trial_ends_at)
                            {{ textByLanguage('تنتهي التجربة', 'Trial ends') }}: {{ $sub['row']->trial_ends_at }}
                        @elseif($sub['row']->current_period_end)
                            {{ textByLanguage('تجديد', 'Renews') }}: {{ $sub['row']->current_period_end }}
                        @endif
                    </p>
                @else
                    <p style="margin:0;font-size:.88rem;color:var(--p-danger);font-weight:600;">
                        <i class="bi bi-exclamation-triangle"></i> {{ textByLanguage('بلا اشتراك في دولة اشتراكات', 'No subscription in a subscription country') }}
                    </p>
                @endif
            </div>

            <div class="p-card">
                <div class="p-card__head">
                    <h3 class="p-card__title" style="margin:0;"><i class="bi bi-signpost-split"></i> {{ textByLanguage('خطوط السفر', 'Travel corridors') }} <span class="svc-count">({{ $co['count'] }})</span></h3>
                    @if(\Illuminate\Support\Facades\Route::has('panel.admin.pricing.corridors.index'))
                        <a href="{{ route('panel.admin.pricing.corridors.index') }}" class="p-btn p-btn--soft"><i class="bi bi-sliders"></i> {{ textByLanguage('إدارة', 'Manage') }}</a>
                    @endif
                </div>

                @if($co['count'] > 0)
                    <div style="max-height:190px;overflow:auto;">
                        @foreach($co['rows'] as $line)
                            <div style="display:flex;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px solid var(--p-border);font-size:.85rem;">
                                <span>{{ $line['from'] }} {{ app()->getLocale() === 'ar' ? '←' : '→' }} {{ $line['to'] }}
                                    <span class="p-cell-sub">{{ $line['sub_service'] }}</span></span>
                                <strong>{{ number_format($line['price'], 2) }} {{ $cur }}</strong>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="margin:0;font-size:.88rem;color:var(--p-danger);font-weight:600;">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ textByLanguage('لا خطوط سفر لهذا المكتب — لن يُعرض على راكب يطلب سفراً.', 'No travel corridors for this office — it is offered to nobody asking for Travel.') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    <div class="p-flash" style="background:rgba(49,40,115,.06);color:var(--p-primary);border:1px solid var(--p-border);">
        <i class="bi bi-info-circle"></i>
        {{ textByLanguage(
            'علّم «يقدّمها» ليظهر المكتب للراكب في هذه الخدمة، واترك السعر فارغاً ليأخذ السعر الأساسي (الرقم الرمادي).',
            'Tick “Offers it” for the office to appear to riders for that service; leave the price empty to charge the base price (the grey number).'
        ) }}
    </div>

    <form method="POST" action="{{ route('panel.admin.office.pricing.update', $office->id) }}">
        @csrf
        @method('PUT')

        @forelse($catalog as $service)
            <div class="p-card" style="margin-bottom:18px;">
                <h3 class="p-card__title"><i class="bi bi-grid-1x2"></i> {{ $service['title'] }}</h3>

                @if(!empty($service['subServices']))
                    <x-panel.table :headers="[
                        textByLanguage('يقدّمها', 'Offers it'),
                        textByLanguage('الخدمة الفرعية', 'Sub-service'),
                        textByLanguage('سعر الفتح', 'Open price'),
                        textByLanguage('سعر الكيلومتر', 'Per km'),
                        textByLanguage('سعر الدقيقة', 'Per minute'),
                    ]">
                        @foreach($service['subServices'] as $sub)
                            @php $p = $prices[$sub['id']] ?? null; @endphp
                            @php $offered = $p !== null ? (bool) ($p['is_enabled'] ?? true) : false; @endphp
                            <tr>
                                <td>
                                    <label class="p-check" style="margin:0;">
                                        <input type="checkbox" name="prices[{{ $sub['id'] }}][enabled]" value="1" @checked(old('prices.'.$sub['id'].'.enabled', $offered))>
                                    </label>
                                </td>
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
