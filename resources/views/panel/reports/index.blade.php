@extends('panel.layouts.master')

@section('title', textByLanguage('التقارير', 'Reports'))
@section('page-title', textByLanguage('التقارير', 'Reports'))

@php
    $money = fn ($m) => number_format(((int) $m) / 100, 2) . ' ' . $currency;

    $fleet  = (int) ($isAdmin ? $summary['fleet_revenue_minor'] : $summary['fleet_commission_minor']);
    $office = (int) ($isAdmin ? $summary['office_payouts_minor'] : $summary['office_earned_minor']);
    $driver = (int) ($isAdmin ? $summary['driver_payouts_minor'] : $summary['driver_paid_minor']);
    $split  = max(1, $fleet + $office + $driver);
    $pct    = fn ($v) => round($v / $split * 100, 1);
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="$isAdmin ? textByLanguage('تقرير الأسطول', 'Fleet report') : textByLanguage('تقرير المكتب', 'Office report')"
        :subtitle="textByLanguage('ملخّص مالي من لقطات العمولة', 'Financial summary from commission snapshots')">
        <x-slot:actions>
            <span class="p-badge"><i class="bi bi-coin"></i> {{ $currency }}</span>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-faq-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:18px;">
        <x-panel.stat :label="textByLanguage('الرحلات', 'Rides')" :value="number_format($summary['rides'])" icon="bi-card-checklist" />
        <x-panel.stat :label="textByLanguage('إجمالي المبيعات', 'Gross')" :value="$money($summary['gross_minor'])" icon="bi-cash-stack" />
        <x-panel.stat :label="textByLanguage('رصيد الإيراد القابل للسحب', 'Withdrawable revenue')" :value="$money($summary['revenue_balance_minor'])" icon="bi-wallet2" variant="primary" />
    </div>

    <x-panel.card :title="textByLanguage('توزيع الدخل', 'Revenue distribution')">
        <x-slot:actions>
            <span style="font-size:.82rem;color:var(--p-text-muted);">{{ textByLanguage('من إجمالي', 'of') }} {{ $money($split) }}</span>
        </x-slot:actions>

        <div class="p-split" style="margin-bottom:20px;">
            <div class="p-split__bar" style="height:16px;">
                <span style="width:{{ $pct($fleet) }}%;" title="{{ $pct($fleet) }}%"></span>
                <span style="width:{{ $pct($office) }}%;" title="{{ $pct($office) }}%"></span>
                <span style="width:{{ $pct($driver) }}%;" title="{{ $pct($driver) }}%"></span>
            </div>
        </div>

        <div class="p-rep-grid">
            <div class="p-rep-line p-rep-line--fleet">
                <span class="p-rep-line__dot"></span>
                <div class="p-rep-line__tx">
                    <span>{{ $isAdmin ? textByLanguage('إيراد الأسطول', 'Fleet revenue') : textByLanguage('عمولة الأسطول', 'Fleet commission') }}</span>
                    <b>{{ $money($fleet) }}</b>
                </div>
                <span class="p-rep-line__pct">{{ $pct($fleet) }}%</span>
            </div>
            <div class="p-rep-line p-rep-line--office">
                <span class="p-rep-line__dot"></span>
                <div class="p-rep-line__tx">
                    <span>{{ $isAdmin ? textByLanguage('مستحقّات المكاتب', 'Office payouts') : textByLanguage('صافي المكتب', 'Office earned') }}</span>
                    <b>{{ $money($office) }}</b>
                </div>
                <span class="p-rep-line__pct">{{ $pct($office) }}%</span>
            </div>
            <div class="p-rep-line p-rep-line--driver">
                <span class="p-rep-line__dot"></span>
                <div class="p-rep-line__tx">
                    <span>{{ $isAdmin ? textByLanguage('مستحقّات السائقين', 'Driver payouts') : textByLanguage('مدفوع للسائقين', 'Driver paid') }}</span>
                    <b>{{ $money($driver) }}</b>
                </div>
                <span class="p-rep-line__pct">{{ $pct($driver) }}%</span>
            </div>
        </div>

        @if($summary['rides'] === 0)
            <p class="p-empty" style="margin-top:14px;"><i class="bi bi-graph-up"></i> {{ textByLanguage('لا توجد بيانات بعد', 'No data yet') }}</p>
        @endif
    </x-panel.card>

@endsection
