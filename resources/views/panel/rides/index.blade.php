@extends('panel.layouts.master')

@section('title', textByLanguage('رحلات الركّاب', 'Rider rides'))
@section('page-title', __('messages.orders'))

@php
    $r = fn ($n) => "panel.{$entity}.{$n}";
    $tone = [
        'matching' => 'warning', 'assigned' => 'primary', 'arriving' => 'primary',
        'arrived' => 'primary', 'on_trip' => 'primary', 'completed' => 'success',
        'cancelled' => 'danger', 'rejected' => 'danger',
    ];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="__('messages.orders')"
        :subtitle="textByLanguage('كل الرحلات القادمة من التطبيقات في هذه الدولة', 'Every ride the apps created in this country')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif

    <div class="p-faq-stats" style="grid-template-columns:repeat(5,1fr);">
        <x-panel.stat :label="textByLanguage('الإجمالي', 'Total')" :value="$counts['total']" icon="bi-collection" />
        <x-panel.stat :label="textByLanguage('مجدولة', 'Scheduled')" :value="$counts['scheduled'] ?? 0" icon="bi-calendar-event" />
        <x-panel.stat :label="textByLanguage('جارية', 'Live')" :value="$counts['live']" icon="bi-broadcast-pin" />
        <x-panel.stat :label="textByLanguage('مكتملة', 'Completed')" :value="$counts['completed']" icon="bi-check2-circle" variant="success" />
        <x-panel.stat :label="textByLanguage('ملغاة', 'Cancelled')" :value="$counts['cancelled']" icon="bi-x-circle" />
    </div>

    <div class="p-card">
        <form method="GET" action="{{ route($r('rides.index')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search ?? '' }}"
                placeholder="{{ textByLanguage('ابحث برقم الرحلة أو نقطة الانطلاق/الوصول', 'Search by ride # or pickup/drop-off') }}">
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                <option value="">{{ textByLanguage('كل الحالات', 'All statuses') }}</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
            @if($statusFilter || ($search ?? '') !== '')
                <a href="{{ route($r('rides.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($rows->count())
            <x-panel.table :headers="array_filter([
                '#', textByLanguage('الراكب', 'Rider'),
                $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('السائق', 'Driver'), textByLanguage('المصدر', 'Source'),
                textByLanguage('المبلغ', 'Amount'), textByLanguage('الحالة', 'Status'),
                textByLanguage('التاريخ', 'When'), '',
            ], fn($h) => $h !== null)">
                @foreach($rows as $row)
                    <tr>
                        <td class="p-row-id">#{{ $row['id'] }}</td>
                        <td>{{ $row['customer'] }}</td>
                        @if($isAdmin)<td>{{ $row['office_id'] ? '#' . $row['office_id'] : '—' }}</td>@endif
                        <td>{{ $row['driver_id'] ? '#' . $row['driver_id'] : '—' }}</td>
                        <td><x-panel.badge :tone="$row['source'] === 'office' ? 'warning' : 'gray'">{{ $row['source'] ?: 'rider' }}</x-panel.badge></td>
                        <td>{{ number_format($row['total_minor'] / 100, 2) }} {{ $row['currency'] }}</td>
                        <td><x-panel.badge :tone="$tone[$row['status']] ?? 'gray'">{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</x-panel.badge></td>
                        <td>{{ $row['created_at'] ? \Illuminate\Support\Carbon::parse($row['created_at'])->diffForHumans() : '—' }}</td>
                        <td><a href="{{ route($r('rides.show'), $row['id']) }}" class="p-btn p-btn--soft"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-signpost-2"></i> {{ textByLanguage('لا توجد رحلات', 'No rides') }}</p>
        @endif
    </div>

@endsection
