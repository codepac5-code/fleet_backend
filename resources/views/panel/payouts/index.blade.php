@extends('panel.layouts.master')

@section('title', textByLanguage('المستحقّات', 'Payouts'))
@section('page-title', textByLanguage('المستحقّات', 'Payouts'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $money = fn ($m, $c) => number_format(((int) $m) / 100, 2) . ' ' . $c;
    $rows = collect($payouts);
    $pendingCount = $rows->where('status', 'pending')->count();
    $pendingSum = (int) $rows->where('status', 'pending')->sum('amount_minor');
    $paidCount = $rows->where('status', 'paid')->count();
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="$isAdmin ? textByLanguage('طلبات الصرف المعلّقة', 'Pending payouts') : textByLanguage('مستحقّاتي', 'My payouts')"
        :subtitle="$isAdmin ? textByLanguage('اعتماد أو رفض طلبات السائقين والمكاتب', 'Approve or reject driver and office requests') : textByLanguage('اطلب صرف رصيد إيرادك', 'Request payout of your revenue balance')" />

    @if(count($payouts))
        <div class="p-faq-stats">
            <x-panel.stat :label="textByLanguage('قيد الانتظار', 'Pending')" :value="$pendingCount" icon="bi-hourglass-split" />
            <x-panel.stat :label="textByLanguage('إجمالي المعلّق', 'Pending amount')" :value="$money($pendingSum, $currency)" icon="bi-cash-stack" />
            <x-panel.stat :label="$isAdmin ? textByLanguage('كل الطلبات', 'All requests') : textByLanguage('تم صرفه', 'Paid')" :value="$isAdmin ? count($payouts) : $paidCount" icon="bi-check2-circle" />
        </div>
    @endif

    @unless($isAdmin)
        <div class="p-card">
            <form method="POST" action="{{ route($r('payouts.request')) }}" style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                @csrf
                <label style="font-size:.85rem;opacity:.7;">{{ textByLanguage('المبلغ (بالوحدة الصغرى)', 'Amount (minor units)') }}</label>
                <input type="number" name="amount_minor" min="1" required class="p-input" style="max-width:180px;" placeholder="10000">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-cash-coin"></i> {{ textByLanguage('طلب صرف', 'Request payout') }}</button>
            </form>
        </div>
    @endunless

    <div class="p-card">
        @if(count($payouts))
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                '#',
                $isAdmin ? textByLanguage('المالك', 'Owner') : null,
                textByLanguage('المصدر', 'Source'),
                textByLanguage('المبلغ', 'Amount'),
                textByLanguage('الحالة', 'Status'),
                $isAdmin ? '' : textByLanguage('المعالجة', 'Processed'),
            ], fn($h) => $h !== null)">
                @foreach($payouts as $p)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($p) ?: '—' }}</x-panel.badge></td>@endif
                        <td>#{{ $p['id'] }}</td>
                        @if($isAdmin)
                            <td>{{ ucfirst($p['owner_type']) }} #{{ $p['owner_id'] }}</td>
                        @endif
                        <td>{{ ucfirst(str_replace('_', ' ', $p['source_account'])) }}</td>
                        <td>{{ $money($p['amount_minor'], $p['currency_code']) }}</td>
                        <td><x-panel.badge :status="$p['status']">{{ ucfirst($p['status']) }}</x-panel.badge></td>
                        @if($isAdmin)
                            <td>
                                <div class="p-row-actions">
                                    <form method="POST" action="{{ route($r('payouts.pay'), $p['id']) }}">
                                        @csrf
                                        @if(shardOf($p))<input type="hidden" name="country" value="{{ shardOf($p) }}">@endif
                                        <button type="submit" class="p-icon-btn" title="{{ textByLanguage('صرف', 'Pay') }}"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route($r('payouts.reject'), $p['id']) }}"
                                        onsubmit="return confirm('{{ textByLanguage('رفض هذا الطلب؟', 'Reject this request?') }}');">
                                        @csrf
                                        @if(shardOf($p))<input type="hidden" name="country" value="{{ shardOf($p) }}">@endif
                                        <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('رفض', 'Reject') }}"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </div>
                            </td>
                        @else
                            <td>{{ $p['processed_at'] ? \Illuminate\Support\Carbon::parse($p['processed_at'])->diffForHumans() : '—' }}</td>
                        @endif
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-cash-stack"></i> {{ textByLanguage('لا توجد طلبات', 'No payouts') }}</p>
        @endif
    </div>

@endsection
