@extends('panel.layouts.master')

@use('App\Http\Services\Panel\Wallet\Logic\PartyLabel')

@section('title', textByLanguage('المعاملات المالية', 'Financial transactions'))
@section('page-title', textByLanguage('المعاملات المالية', 'Financial transactions'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $statuses = [
        'completed' => textByLanguage('مكتمل', 'Completed'),
        'pending'   => textByLanguage('معلّق', 'Pending'),
        'failed'    => textByLanguage('فاشل', 'Failed'),
    ];
@endphp

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('المعاملات المالية', 'Financial transactions')"
        :subtitle="textByLanguage('سجلّ الحركات المالية للمحفظة', 'Wallet movement ledger')">
        <x-slot:actions>
            <a href="{{ route($r('wallet.transactions.export')) }}" class="p-btn p-btn--ghost"><i class="bi bi-download"></i> {{ textByLanguage('تصدير CSV', 'Export CSV') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    @php $walletTones = ['violet', 'gold', 'royal', 'plum']; @endphp
    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        @foreach($summary as $s)
            <x-panel.stat :variant="$walletTones[$loop->index % count($walletTones)]" wave :label="$s['label']" :icon="$s['icon']"
                :value="$s['money'] ? getPriceFormat($s['value']) : number_format((int) $s['value'])" />
        @endforeach
    </div>

    @if(!empty($subscriptionPayments))
        <div class="p-card" style="margin-bottom:18px;">
            <div class="p-card__head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-award"></i> {{ textByLanguage('دفعات الاشتراكات', 'Subscription payments') }}</h3>
                @if($isAdmin && \Illuminate\Support\Facades\Route::has('panel.admin.subscriptions.index'))
                    <a href="{{ route('panel.admin.subscriptions.index') }}" class="p-btn p-btn--soft"><i class="bi bi-box-arrow-up-right"></i> {{ textByLanguage('الاشتراكات', 'Subscriptions') }}</a>
                @endif
            </div>

            <x-panel.table :headers="[
                textByLanguage('المكتب', 'Office'),
                textByLanguage('الخطة', 'Plan'),
                textByLanguage('المبلغ', 'Amount'),
                textByLanguage('التاريخ', 'Date'),
            ]">
                @foreach($subscriptionPayments as $p)
                    <tr>
                        <td><strong>{{ $p['office'] }}</strong></td>
                        <td>{{ ucfirst($p['plan']) }}</td>
                        <td><span class="tx-in">+{{ number_format($p['amount_minor'] / 100, 2) }} {{ $p['currency'] }}</span></td>
                        <td style="color:var(--p-text-muted);white-space:nowrap;">{{ $p['at'] ?: '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>

            <p class="p-cell-sub" style="margin-top:8px;">
                {{ textByLanguage('دفعات مُحصَّلة عبر مزوّد الدفع ومقيَّدة في دفتر الأستاذ — ليست حركات محفظة، لذلك لا تظهر في الجدول أدناه.', 'Collected through the payment provider and posted to the ledger — these are not wallet movements, so they are not in the table below.') }}
            </p>
        </div>
    @endif

    <div class="p-card">
        <form method="GET" action="{{ route($r('wallet.transactions')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="{{ textByLanguage('ابحث بالمرجع أو الوصف أو الرقم', 'Search by reference, description or #') }}">
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" @selected($statusFilter === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @if($search || $statusFilter)
                <a href="{{ route($r('wallet.transactions')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($transactions->count())
            <x-panel.table :headers="[
                '#',
                textByLanguage('من', 'From'),
                textByLanguage('إلى', 'To'),
                textByLanguage('المبلغ', 'Amount'),
                textByLanguage('الوصف', 'Description'),
                textByLanguage('الحالة', 'Status'),
                textByLanguage('التاريخ', 'Date'),
            ]">
                @foreach($transactions as $t)
                    <tr>
                        <td><strong>#{{ $t->id }}</strong></td>
                        <td>
                            <span class="tx-party"><i class="bi {{ PartyLabel::icon($t->from_type) }}"></i> {{ $t->from_name }}</span>
                            <span class="p-cell-sub">{{ $t->from_label }}</span>
                        </td>
                        <td>
                            <span class="tx-party"><i class="bi {{ PartyLabel::icon($t->to_type) }}"></i> {{ $t->to_name }}</span>
                            <span class="p-cell-sub">{{ $t->to_label }}</span>
                        </td>
                        <td>
                            <span class="@if($t->direction === 'in') tx-in @elseif($t->direction === 'out') tx-out @endif">
                                @if($t->direction === 'in'){{ '+' }}@elseif($t->direction === 'out'){{ '−' }}@endif{{ getPriceFormat($t->amount ?? 0) }}
                            </span>
                        </td>
                        <td style="max-width:240px;">{{ $t->description ?: ($t->description_en ?: '—') }}</td>
                        <td><x-panel.badge :status="$t->status">{{ $statuses[$t->status] ?? $t->status }}</x-panel.badge></td>
                        <td style="color:var(--p-text-muted);white-space:nowrap;">{{ $t->created_at ? $t->created_at->format('Y-m-d H:i') : '—' }}</td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($transactions->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $transactions->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $transactions->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}</span>
                    <a class="p-page {{ ! $transactions->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $transactions->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty">
                <i class="bi bi-wallet2"></i>
                {{ ($search || $statusFilter) ? textByLanguage('لا توجد نتائج مطابقة', 'No matching results') : textByLanguage('لا توجد معاملات بعد', 'No transactions yet') }}
            </p>
        @endif
    </div>

@endsection
