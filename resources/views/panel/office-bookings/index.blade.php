@extends('panel.layouts.master')

@section('title', textByLanguage('الحجوزات المكتبية', 'Office bookings'))
@section('page-title', textByLanguage('الحجوزات المكتبية', 'Office bookings'))

@php
    $r = fn ($n) => "panel.{$entity}.{$n}";
    $canRefund = \Illuminate\Support\Facades\Route::has("panel.{$entity}.booking.refund")
        && (auth()->guard($entity)->user()?->can(\App\Http\Services\Panel\Shared\Authorization\PanelPermission::EDIT_COMMISSION) ?? false);
    $tone = [
        'matching' => 'warning', 'assigned' => 'primary', 'arriving' => 'primary',
        'arrived' => 'primary', 'on_trip' => 'primary', 'completed' => 'success',
        'cancelled' => 'danger', 'rejected' => 'danger',
    ];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('الحجوزات المكتبية', 'Office bookings')"
        :subtitle="textByLanguage('الرحلات التي أنشأها المكتب يدوياً', 'Rides created manually by the office')">
        <x-slot:actions>
            <a href="{{ route($r('office-bookings.create')) }}" class="p-btn p-btn--primary"><i class="bi bi-plus-lg"></i> {{ textByLanguage('حجز جديد', 'New booking') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-faq-stats" style="grid-template-columns:repeat(4,1fr);">
        <x-panel.stat :label="textByLanguage('بانتظار سائق', 'Matching')" :value="$counts['matching']" icon="bi-hourglass-split" />
        <x-panel.stat :label="textByLanguage('قيد التنفيذ', 'In progress')" :value="$counts['assigned']" icon="bi-truck" />
        <x-panel.stat :label="textByLanguage('مكتملة', 'Completed')" :value="$counts['completed']" icon="bi-check2-circle" />
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-collection" />
    </div>

    <div class="p-card">
        @if(count($rows))
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                '#', textByLanguage('العميل', 'Customer'), textByLanguage('السائق', 'Driver'),
                textByLanguage('المبلغ', 'Amount'), textByLanguage('الدفع', 'Payment'),
                textByLanguage('الحالة', 'Status'), textByLanguage('التاريخ', 'When'),
                $canRefund ? textByLanguage('استرداد', 'Refund') : null,
            ], fn($h) => $h !== null)">
                @foreach($rows as $row)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($row) ?: '—' }}</x-panel.badge></td>@endif
                        <td class="p-row-id">#{{ $row['id'] }}</td>
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $row['customer'] }}</strong>
                                <span class="p-cell-sub" dir="ltr">{{ $row['phone'] }}</span>
                            </div></div>
                        </td>
                        <td>{{ $row['driver_id'] ? '#' . $row['driver_id'] : '—' }}</td>
                        <td>
                            {{ number_format($row['total_minor'] / 100, 2) }} {{ $row['currency'] }}
                            @if($row['waiting_minor'] > 0 || $row['tip_minor'] > 0)
                                <span class="p-cell-sub">
                                    @if($row['waiting_minor'] > 0){{ textByLanguage('انتظار', 'Wait') }} {{ number_format($row['waiting_minor'] / 100, 2) }}@endif
                                    @if($row['tip_minor'] > 0) · {{ textByLanguage('إكرامية', 'Tip') }} {{ number_format($row['tip_minor'] / 100, 2) }}@endif
                                </span>
                            @endif
                        </td>
                        <td><x-panel.badge :tone="$row['payment_method'] === 'cash' ? 'gray' : 'primary'">{{ $row['payment_method'] === 'cash' ? textByLanguage('نقدي', 'Cash') : textByLanguage('محفظة', 'Wallet') }}</x-panel.badge></td>
                        <td><x-panel.badge :tone="$tone[$row['status']] ?? 'gray'">{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</x-panel.badge></td>
                        <td>{{ $row['created_at'] ? \Illuminate\Support\Carbon::parse($row['created_at'])->diffForHumans() : '—' }}</td>
                        @if($canRefund)
                            <td>
                                @if($row['status'] === 'completed' && $row['payment_method'] !== 'cash' && $row['total_minor'] > 0)
                                    <form method="POST" action="{{ route("panel.{$entity}.booking.refund", $row['id']) }}"
                                          onsubmit="var a=prompt('{{ textByLanguage('مبلغ الاسترداد', 'Refund amount') }} ({{ $row['currency'] }})', '{{ number_format($row['total_minor'] / 100, 2, '.', '') }}'); if(a===null){return false;} this.amount.value=a;">
                                        @csrf
                                        <input type="hidden" name="amount" value="">
                                        <button type="submit" class="p-btn p-btn--soft"><i class="bi bi-arrow-counterclockwise"></i> {{ textByLanguage('استرداد', 'Refund') }}</button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-clipboard-plus"></i> {{ textByLanguage('لا توجد حجوزات مكتبية بعد — أنشئ أوّل حجز.', 'No office bookings yet — create your first.') }}</p>
        @endif
    </div>

@endsection
