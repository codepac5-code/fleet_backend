@extends('panel.layouts.master')

@section('title', textByLanguage('خطط الاشتراك', 'Subscription plans'))
@section('page-title', textByLanguage('خطط الاشتراك', 'Subscription plans'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $ar = app()->getLocale() === 'ar';
    $t = fn ($en, $arText) => $ar ? $arText : $en;
    $money = fn ($m, $c) => $m === null ? $t('Custom', 'مخصّص') : (number_format($m / 100, 2) . ' ' . ($c ?: ''));
    $lim = fn ($v) => $v === null ? $t('Unlimited', 'بلا حد') : $v;
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('خطط الاشتراك', 'Subscription plans')"
        :subtitle="textByLanguage('خطط المكاتب (مشتركة لكل الدول) — الأسعار والحدود والعمولة', 'Office plans (platform-wide) — prices, limits and commission')">
        <x-slot:actions>
            <a href="{{ route($r('plans.create')) }}" class="p-btn p-btn--primary"><i class="bi bi-plus-lg"></i> {{ $t('New plan', 'خطة جديدة') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif

    <div class="p-card">
        @if($plans->count())
            <x-panel.table :headers="[
                $t('Plan', 'الخطة'), $t('Price', 'السعر'), $t('Commission', 'العمولة'),
                $t('Drivers', 'سائقون'), $t('Rides', 'رحلات'), $t('Overage', 'رسوم التجاوز'),
                $t('Trial', 'تجربة'), $t('State', 'الحالة'), '',
            ]">
                @foreach($plans as $p)
                    <tr>
                        <td><strong>{{ $p->name }}</strong> <code style="font-size:.75rem;color:var(--p-text-muted)">{{ $p->key }}</code>
                            @if($p->is_popular)<x-panel.badge tone="primary">{{ $t('Popular', 'شائعة') }}</x-panel.badge>@endif</td>
                        <td>{{ $money($p->price_minor, $p->currency_code) }}</td>
                        <td>{{ $p->fleet_commission_rate === null ? '—' : $p->fleet_commission_rate . '%' }}</td>
                        <td>{{ $lim($p->driver_limit) }}</td>
                        <td>{{ $lim($p->ride_limit) }}</td>
                        <td style="font-size:.82rem;">
                            @if($p->extra_ride_minor || $p->extra_driver_minor)
                                @if($p->extra_ride_minor){{ $t('ride', 'رحلة') }}: {{ number_format($p->extra_ride_minor / 100, 2) }}@endif
                                @if($p->extra_driver_minor) · {{ $t('driver', 'سائق') }}: {{ number_format($p->extra_driver_minor / 100, 2) }}@endif
                            @else — @endif
                        </td>
                        <td>{{ (int) $p->trial_days }}{{ $t('d', 'ي') }}</td>
                        <td><x-panel.badge :tone="$p->is_active ? 'success' : 'gray'">{{ $p->is_active ? $t('Active', 'مفعّلة') : $t('Off', 'موقوفة') }}</x-panel.badge></td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route($r('plans.edit'), $p->id) }}" class="p-btn p-btn--soft"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route($r('plans.toggle'), $p->id) }}" style="display:inline;">@csrf
                                <button type="submit" class="p-btn p-btn--soft">{{ $p->is_active ? $t('Disable', 'إيقاف') : $t('Enable', 'تفعيل') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-collection"></i> {{ $t('No plans yet', 'لا توجد خطط بعد') }}</p>
        @endif
    </div>

@endsection
