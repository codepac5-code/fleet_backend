@extends('panel.layouts.master')

@section('title', textByLanguage('المركبات', 'Vehicles'))
@section('page-title', textByLanguage('المركبات', 'Vehicles'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="textByLanguage('المركبات', 'Vehicles')"
        :subtitle="textByLanguage('إدارة أسطول المركبات', 'Manage the vehicle fleet')">
        <x-slot:actions>
            @if(shardIsAll())
                <span class="p-btn p-btn--ghost"><i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة مركبة في', 'Add vehicle in') }}</span>
                @foreach(($panelCountries ?? []) as $pc)
                    <a href="{{ route($r('vehicle.create')) }}?country={{ $pc->id }}" class="p-btn p-btn--primary" style="margin-inline-start:6px;">{{ $pc->country_code ?: $pc->name }}</a>
                @endforeach
            @else
                <a href="{{ route($r('vehicle.create')) }}" class="p-btn p-btn--primary">
                    <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة مركبة', 'Add vehicle') }}
                </a>
            @endif
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route($r('vehicle.index')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="{{ textByLanguage('ابحث بالماركة أو الطراز أو اللوحة', 'Search by brand, model or plate') }}">
            @if($isAdmin && !empty($officeOptions))
                <select name="office" onchange="this.form.submit()" class="p-search__select">
                    <option value="">{{ textByLanguage('كل المكاتب', 'All offices') }}</option>
                    @foreach($officeOptions as $id => $oName)
                        <option value="{{ $id }}" @selected($officeFilter == $id)>{{ $oName }}</option>
                    @endforeach
                </select>
            @endif
            @if($search || $officeFilter)<a href="{{ route($r('vehicle.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($vehicles->count())
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('المركبة', 'Vehicle'),
                $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('السائق', 'Driver'),
                textByLanguage('المقاعد', 'Seats'),
                textByLanguage('النوع', 'Type'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($vehicles as $v)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($v) ?: '—' }}</x-panel.badge></td>@endif
                        <td>
                            <div class="p-cell-main">
                                @if($v->photo)<span class="p-avatar p-avatar--img"><img src="{{ asset('storage/'.$v->photo) }}" alt=""></span>@else<span class="p-avatar"><i class="bi bi-car-front"></i></span>@endif
                                <div>
                                    <strong>{{ trim($v->vehicleBrand.' '.$v->model) }}</strong>
                                    <span class="p-cell-sub" dir="ltr">{{ $v->plate }} · {{ $v->modelYear }}</span>
                                </div>
                            </div>
                        </td>
                        @if($isAdmin)<td>{{ $officeOptions[$v->officeId] ?? '—' }}</td>@endif
                        <td>{{ $v->driverId ? ($driverOptions[$v->driverId] ?? ('#'.$v->driverId)) : '—' }}</td>
                        <td>{{ $v->seatsCount ?: '—' }}</td>
                        <td>
                            <x-panel.badge :tone="$v->fleet_car ? 'primary' : 'gray'">
                                {{ $v->fleet_car ? textByLanguage('سيارة أسطول', 'Fleet car') : textByLanguage('خاصة', 'Private') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ shardLink($r('vehicle.services.edit'), $v->id, $v) }}" class="p-icon-btn" title="{{ textByLanguage('الخدمات', 'Services') }}"><i class="bi bi-diagram-3"></i></a>
                                <a href="{{ shardLink($r('vehicle.edit'), $v->id, $v) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route($r('vehicle.destroy'), $v->id) }}" onsubmit="return confirm('{{ textByLanguage('حذف هذه المركبة؟', 'Delete this vehicle?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    @if(shardOf($v))<input type="hidden" name="country" value="{{ shardOf($v) }}">@endif
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($vehicles->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $vehicles->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $vehicles->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $vehicles->currentPage() }} / {{ $vehicles->lastPage() }}</span>
                    <a class="p-page {{ ! $vehicles->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $vehicles->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty"><i class="bi bi-car-front"></i> {{ ($search || $officeFilter) ? textByLanguage('لا توجد نتائج', 'No results') : textByLanguage('لا توجد مركبات بعد', 'No vehicles yet') }}</p>
        @endif
    </div>

@endsection
