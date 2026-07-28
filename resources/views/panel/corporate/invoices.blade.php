@extends('panel.layouts.master')

@section('title', textByLanguage('فوترة حسابات الأعمال', 'Business-account billing'))
@section('page-title', textByLanguage('فوترة حسابات الأعمال', 'Business-account billing'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $statuses = ['unbilled', 'due', 'paid'];
    $tone = ['unbilled' => 'gray', 'due' => 'warning', 'paid' => 'success'];
    $money = fn ($m, $c) => number_format(((int) $m) / 100, 2) . ' ' . ($c ?: '');
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('فوترة حسابات الأعمال (ركّاب)', 'Business-account billing (riders)')"
        :subtitle="textByLanguage('فواتير رحلات شهرية لحسابات أعمال في تطبيق الراكب — مستقلّة عن المكاتب', 'Monthly ride invoices for rider-app business accounts — separate from offices')" />

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('غير مفوترة', 'Unbilled')" :value="$counts['unbilled']" icon="bi-file-earmark" />
        <x-panel.stat :label="textByLanguage('مستحقّة', 'Due')" :value="$counts['due']" icon="bi-hourglass-split" :variant="$counts['due'] ? 'primary' : null" />
        <x-panel.stat :label="textByLanguage('إجمالي المستحقّ', 'Total due')" :value="$money($counts['due_minor'], '')" icon="bi-cash-stack" />
        <x-panel.stat :label="textByLanguage('مدفوعة', 'Paid')" :value="$counts['paid']" icon="bi-check2-circle" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('corporate.invoices')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)<option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            @if($statusFilter)<a href="{{ route($r('corporate.invoices')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
        </form>

        @if($invoices->count())
            <x-panel.table :headers="[
                '#', textByLanguage('الحساب', 'Account'), textByLanguage('الشهر', 'Month'),
                textByLanguage('الرحلات', 'Trips'), textByLanguage('المبلغ', 'Amount'),
                textByLanguage('الحالة', 'Status'), '',
            ]">
                @foreach($invoices as $inv)
                    <tr>
                        <td class="p-row-id">#{{ $inv->id }}</td>
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $inv->user ? trim(($inv->user->firstName ?? '') . ' ' . ($inv->user->lastName ?? '')) : ('#' . $inv->user_id) }}</strong>
                                <span class="p-cell-sub" dir="ltr">{{ $inv->user->phoneNumber ?? '' }}</span>
                            </div></div>
                        </td>
                        <td dir="ltr" style="text-align:start;">{{ $inv->month }}</td>
                        <td>{{ number_format($inv->trips) }}</td>
                        <td>{{ $money($inv->amount_minor, $inv->currency_code) }}</td>
                        <td><x-panel.badge :tone="$tone[$inv->status] ?? 'gray'">{{ ucfirst($inv->status) }}</x-panel.badge></td>
                        <td>
                            @if($inv->status !== 'paid')
                                <form method="POST" action="{{ route($r('corporate.invoices.status'), $inv->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="p-btn p-btn--ghost"><i class="bi bi-check2-circle"></i> {{ textByLanguage('تعليم مدفوعة', 'Mark paid') }}</button>
                                </form>
                            @else
                                <span class="p-cell-sub"><i class="bi bi-check-lg"></i> {{ textByLanguage('مدفوعة', 'Paid') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-building"></i> {{ textByLanguage('لا توجد فواتير', 'No invoices') }}</p>
        @endif
    </div>

@endsection
