@extends('panel.layouts.master')

@section('title', textByLanguage('اشتراكات المكاتب', 'Office subscriptions'))
@section('page-title', textByLanguage('اشتراكات المكاتب', 'Office subscriptions'))

@php
    $r = fn ($name) => "panel.admin.{$name}";
    $statuses = ['trialing', 'active', 'past_due', 'canceled', 'ended'];
    $tone = [
        'trialing' => 'primary', 'active' => 'success', 'past_due' => 'danger',
        'canceled' => 'gray', 'ended' => 'gray',
    ];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('اشتراكات المكاتب', 'Office subscriptions')"
        :subtitle="textByLanguage('متابعة التجارب والاشتراكات النشطة وحالات فشل الدفع في الدولة الحالية', 'Track trials, active subscriptions and payment failures for the current country')" />

    <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
        <a href="{{ route($r('overage-invoices.index')) }}" class="p-btn p-btn--sm"><i class="bi bi-receipt"></i> {{ textByLanguage('فواتير التجاوز', 'Overage invoices') }}</a>
    </div>

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('تجربة', 'Trialing')" :value="$counts['trialing']" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('نشط', 'Active')" :value="$counts['active']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('فشل الدفع', 'Past due')" :value="$counts['past_due']" icon="bi-exclamation-octagon" :variant="$counts['past_due'] ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-collection" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('subscriptions.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            @if($statusFilter)<a href="{{ route($r('subscriptions.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($subscriptions->count())
            <x-panel.table :headers="[
                textByLanguage('المكتب', 'Office'),
                textByLanguage('الخطة', 'Plan'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('السعر', 'Price'),
                textByLanguage('تجاوز مجمّع', 'Accrued overage'),
                textByLanguage('التجربة / التجديد', 'Trial / renews'),
                textByLanguage('المزوّد', 'Provider'),
            ]">
                @foreach($subscriptions as $sub)
                    <tr @if($sub->status === 'past_due') style="background:rgba(220,38,38,.06);" @endif>
                        <td>
                            <div class="p-cell-main">
                                <div>
                                    <strong>{{ $officeNames[$sub->office_id] ?? (textByLanguage('مكتب', 'Office') . ' #' . $sub->office_id) }}</strong>
                                    <span class="p-cell-sub">#{{ $sub->office_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ ucfirst($sub->plan_key) }}</td>
                        <td><x-panel.badge :tone="$tone[$sub->status] ?? 'gray'">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</x-panel.badge></td>
                        <td>{{ number_format(((int) $sub->price_minor) / 100, 2) }} {{ $sub->currency_code }}</td>
                        <td>
                            @php $ov = $overageByOffice[$sub->office_id] ?? 0; @endphp
                            @if($ov > 0)
                                <strong style="color:#dc2626;">{{ number_format($ov / 100, 2) }} {{ $sub->currency_code }}</strong>
                            @else
                                <span class="p-cell-sub">—</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->status === 'trialing' && $sub->trial_ends_at)
                                <i class="bi bi-hourglass-split" style="color:var(--p-primary);"></i> {{ \Illuminate\Support\Carbon::parse($sub->trial_ends_at)->isoFormat('D MMM YYYY') }}
                            @elseif($sub->current_period_end)
                                <i class="bi bi-arrow-repeat"></i> {{ \Illuminate\Support\Carbon::parse($sub->current_period_end)->isoFormat('D MMM YYYY') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($sub->provider_subscription_id)
                                <span class="p-badge p-badge--gray" title="{{ $sub->provider_subscription_id }}"><i class="bi bi-stripe"></i> Stripe</span>
                            @else
                                <span class="p-cell-sub">{{ textByLanguage('يدوي', 'Manual') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-collection"></i> {{ textByLanguage('لا توجد اشتراكات', 'No subscriptions') }}</p>
        @endif
    </div>

@endsection
