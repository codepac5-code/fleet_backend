@extends('panel.layouts.master')

@section('title', textByLanguage('وضع فوترة الدول', 'Region billing'))
@section('page-title', textByLanguage('وضع فوترة الدول', 'Region billing'))

@php $r = fn ($name) => "panel.admin.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('نموذج الفوترة لكل دولة', 'Per-country billing model')"
        :subtitle="textByLanguage('العمولة: بلا اشتراك. الاشتراك: تسجيل ذاتي + تجربة + فوترة Stripe متكرّرة.', 'Commission: no subscription. Subscription: self-signup + trial + recurring Stripe billing.')" />

    <div class="p-card">
        @if(count($countries))
            <x-panel.table :headers="[
                textByLanguage('الدولة', 'Country'),
                textByLanguage('الرمز', 'Code'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('وضع الفوترة', 'Billing mode'),
                '',
            ]">
                @foreach($countries as $c)
                    <tr>
                        <td><strong>{{ $c['name'] }}</strong></td>
                        <td dir="ltr" style="text-align:start;">{{ $c['country_code'] ?: '—' }}</td>
                        <td>
                            <x-panel.badge :tone="$c['is_active'] ? 'success' : 'gray'">
                                {{ $c['is_active'] ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Off') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            @if($c['mode'] === 'subscription')
                                <x-panel.badge tone="primary"><i class="bi bi-stars"></i> {{ textByLanguage('اشتراك', 'Subscription') }}</x-panel.badge>
                            @else
                                <x-panel.badge tone="warning"><i class="bi bi-percent"></i> {{ textByLanguage('عمولة', 'Commission') }}</x-panel.badge>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route($r('regions.billing.update'), $c['id']) }}" class="p-reg-switch">
                                @csrf
                                <select name="billing_mode" onchange="this.form.submit()" class="p-search__select">
                                    <option value="commission" @selected($c['mode'] === 'commission')>{{ textByLanguage('عمولة', 'Commission') }}</option>
                                    <option value="subscription" @selected($c['mode'] === 'subscription')>{{ textByLanguage('اشتراك', 'Subscription') }}</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-globe2"></i> {{ textByLanguage('لا توجد دول مُعرّفة', 'No countries defined') }}</p>
        @endif
    </div>

    <p class="p-plan-note" style="margin-top:14px;">
        <i class="bi bi-info-circle"></i>
        {{ textByLanguage('تبديل دولة إلى «اشتراك» يفتح التسجيل الذاتي للمكاتب فيها ويطلب اشتراكاً؛ الرحلات تبقى تُسوّى بنِسب الخطة.', 'Switching a country to “Subscription” opens self-signup for its offices and requires a plan; rides still settle at the plan rates.') }}
    </p>

@endsection
