@extends('panel.layouts.master')

@section('title', textByLanguage('فواتير التجاوز', 'Overage invoices'))
@section('page-title', textByLanguage('فواتير التجاوز', 'Overage invoices'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $statuses = ['invoiced', 'collected'];
    $tone = ['invoiced' => 'danger', 'collected' => 'success'];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('فواتير التجاوز', 'Overage invoices')"
        :subtitle="textByLanguage('رسوم تجاوز الخطة المجمّعة والمُغلقة في فواتير، وحالة تحصيلها — للدولة الحالية', 'Accrued plan-overage closed into invoices and their collection status — for the current country')" />

    <a id="ovLiveNotice" href="{{ route($r('overage-invoices.index')) }}" style="display:none; align-items:center; gap:6px; margin-bottom:12px; padding:8px 12px; border-radius:8px; background:rgba(220,38,38,.08); color:#dc2626; font-weight:600; text-decoration:none;">
        <i class="bi bi-arrow-repeat"></i>
        {{ textByLanguage('فواتير تجاوز جديدة', 'New overage invoices') }} (<span data-ov-count>0</span>) — {{ textByLanguage('حدّث', 'refresh') }}
    </a>

    <div class="p-faq-stats" style="grid-template-columns:repeat(2,1fr);">
        <x-panel.stat :label="textByLanguage('بانتظار التحصيل', 'Awaiting collection')" :value="number_format($pendingMinor / 100, 2)" icon="bi-hourglass-split" :variant="$pendingMinor ? 'danger' : null" />
        <x-panel.stat :label="textByLanguage('محصّل', 'Collected')" :value="number_format($collectedMinor / 100, 2)" icon="bi-check2-circle" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('overage-invoices.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @if($statusFilter)<a href="{{ route($r('overage-invoices.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
            <a href="{{ route($r('overage-invoices.export'), $statusFilter ? ['status' => $statusFilter] : []) }}" class="p-btn p-btn--sm" style="margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}:auto;"><i class="bi bi-download"></i> {{ textByLanguage('تصدير CSV', 'Export CSV') }}</a>
        </form>

        @if(count($invoices))
            <x-panel.table :headers="[
                textByLanguage('الفاتورة', 'Invoice'),
                textByLanguage('المكتب', 'Office'),
                textByLanguage('الفترة', 'Period'),
                textByLanguage('المبلغ', 'Amount'),
                textByLanguage('البنود', 'Items'),
                textByLanguage('التحصيل', 'Collection'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('إجراء', 'Action'),
            ]">
                @foreach($invoices as $inv)
                    <tr>
                        <td><strong>{{ $inv['invoice_ref'] }}</strong></td>
                        <td>
                            {{ $officeNames[$inv['office_id']] ?? (textByLanguage('مكتب', 'Office') . ' #' . $inv['office_id']) }}
                            <span class="p-cell-sub">#{{ $inv['office_id'] }}</span>
                        </td>
                        <td>{{ $inv['period'] }}</td>
                        <td><strong>{{ number_format($inv['total_minor'] / 100, 2) }} {{ $inv['currency'] }}</strong></td>
                        <td>{{ $inv['items'] }}</td>
                        <td>
                            @if(($inv['collection_method'] ?? 'manual') === 'stripe')
                                <span class="p-badge p-badge--gray" title="{{ $inv['external_ref'] }}"><i class="bi bi-stripe"></i> Stripe</span>
                            @else
                                <span class="p-cell-sub">{{ textByLanguage('يدوي', 'Manual') }}</span>
                            @endif
                        </td>
                        <td><x-panel.badge :tone="$tone[$inv['status']] ?? 'gray'">{{ ucfirst($inv['status']) }}</x-panel.badge></td>
                        <td>
                            @if($inv['status'] === 'invoiced')
                                <form method="POST" action="{{ route($r('overage-invoices.collect'), ['ref' => $inv['invoice_ref']]) }}" onsubmit="return confirm('{{ textByLanguage('تأكيد تحصيل هذه الفاتورة؟', 'Mark this invoice collected?') }}');">
                                    @csrf
                                    <button type="submit" class="p-btn p-btn--sm p-btn--success"><i class="bi bi-check2"></i> {{ textByLanguage('تحصيل', 'Collect') }}</button>
                                </form>
                            @else
                                <span class="p-cell-sub">{{ $inv['collected_at'] ? \Illuminate\Support\Carbon::parse($inv['collected_at'])->isoFormat('D MMM YYYY') : '—' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-receipt"></i> {{ textByLanguage('لا توجد فواتير تجاوز', 'No overage invoices') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var notice = document.getElementById('ovLiveNotice');
    if (!notice) return;
    var count = 0;

    window.addEventListener('fleet:rt:overage.invoiced', function (e) {
        var d = (e.detail && e.detail.data) || {};
        if (d.invoice_ref == null) return;
        count++;
        var c = notice.querySelector('[data-ov-count]');
        if (c) c.textContent = count;
        notice.style.display = 'inline-flex';
    });
})();
</script>
@endpush
