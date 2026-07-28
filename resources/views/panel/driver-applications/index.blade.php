@extends('panel.layouts.master')

@section('title', textByLanguage('طلبات السائقين', 'Driver applications'))
@section('page-title', textByLanguage('طلبات السائقين', 'Driver applications'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $statuses = ['pending', 'approved', 'rejected'];
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('طلبات انضمام / ربط السائقين', 'Driver join / link requests')"
        :subtitle="textByLanguage('راجع الطلبات ثم أنشئ السائق من إدارة السائقين', 'Review requests, then add the driver from Drivers')" />

    <div class="p-card">
        <form method="GET" action="{{ route($r('driver-applications.index')) }}" class="p-search">
            <i class="bi bi-funnel"></i>
            <select name="status" onchange="this.form.submit()" class="p-search__select">
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected($statusFilter === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </form>

        @if($applications->count())
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                '#', textByLanguage('الاسم', 'Name'), textByLanguage('الهاتف', 'Phone'),
                textByLanguage('المركبة', 'Vehicle'), textByLanguage('النوع', 'Type'),
                $isAdmin ? textByLanguage('المكتب', 'Office') : null, textByLanguage('الحالة', 'Status'), '',
            ], fn($h) => $h !== null)">
                @foreach($applications as $a)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($a) ?: '—' }}</x-panel.badge></td>@endif
                        <td>#{{ $a->id }}</td>
                        <td>{{ $a->name ?: '—' }}@if($a->city)<span class="p-cell-sub">{{ $a->city }}</span>@endif</td>
                        <td dir="ltr" style="text-align:start;">{{ $a->phone }}</td>
                        <td>{{ $a->vehicle_type ?: '—' }}@if($a->license_number)<span class="p-cell-sub">{{ $a->license_number }}</span>@endif</td>
                        <td><x-panel.badge :tone="$a->kind === 'link' ? 'primary' : 'warning'">{{ ucfirst($a->kind) }}</x-panel.badge></td>
                        @if($isAdmin)<td>{{ $a->office_id ? '#' . $a->office_id : '—' }}</td>@endif
                        <td><x-panel.badge :status="$a->status">{{ ucfirst($a->status) }}</x-panel.badge></td>
                        <td>
                            @if($a->status === 'pending')
                                <div class="p-row-actions">
                                    <form method="POST" action="{{ route($r('driver-applications.review'), $a->id) }}">
                                        @csrf
                                        @if(shardOf($a))<input type="hidden" name="country" value="{{ shardOf($a) }}">@endif
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="p-icon-btn" title="{{ textByLanguage('قبول', 'Approve') }}"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route($r('driver-applications.review'), $a->id) }}"
                                        onsubmit="return confirm('{{ textByLanguage('رفض هذا الطلب؟', 'Reject this request?') }}');">
                                        @csrf
                                        @if(shardOf($a))<input type="hidden" name="country" value="{{ shardOf($a) }}">@endif
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('رفض', 'Reject') }}"><i class="bi bi-x-circle"></i></button>
                                    </form>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-person-plus"></i> {{ textByLanguage('لا توجد طلبات', 'No applications') }}</p>
        @endif
    </div>

@endsection
