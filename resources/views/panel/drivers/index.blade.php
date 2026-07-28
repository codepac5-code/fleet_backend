@extends('panel.layouts.master')

@section('title', __('messages.drivers'))
@section('page-title', __('messages.drivers'))

@php $r = fn ($name) => "panel.{$entity}.{$name}"; @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="__('messages.drivers')"
        :subtitle="textByLanguage('إدارة السائقين والمركبات', 'Manage drivers and vehicles')">
        <x-slot:actions>
            <a href="{{ route($r('driver.export')) }}" class="p-btn p-btn--ghost"><i class="bi bi-download"></i> {{ textByLanguage('تصدير CSV', 'Export CSV') }}</a>
            @if(shardIsAll())
                <span class="p-btn p-btn--ghost"><i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة سائق في', 'Add driver in') }}</span>
                @foreach(($panelCountries ?? []) as $pc)
                    <a href="{{ route($r('driver.create')) }}?country={{ $pc->id }}" class="p-btn p-btn--primary" style="margin-inline-start:6px;">{{ $pc->country_code ?: $pc->name }}</a>
                @endforeach
            @else
                <a href="{{ route($r('driver.create')) }}" class="p-btn p-btn--primary">
                    <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة سائق', 'Add driver') }}
                </a>
            @endif
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route($r('driver.index')) }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="{{ textByLanguage('ابحث بالاسم أو الهاتف', 'Search by name or phone') }}">
            @if($isAdmin && !empty($officeOptions))
                <select name="office" onchange="this.form.submit()" class="p-search__select">
                    <option value="">{{ textByLanguage('كل المكاتب', 'All offices') }}</option>
                    @foreach($officeOptions as $id => $name)
                        <option value="{{ $id }}" @selected($officeFilter == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            @if($search || $officeFilter)
                <a href="{{ route($r('driver.index')) }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($drivers->count())
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('السائق', 'Driver'),
                textByLanguage('الهاتف', 'Phone'),
                $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('الرحلات', 'Rides'),
                textByLanguage('التقييم', 'Rating'),
                textByLanguage('الحالة', 'Status'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($drivers as $driver)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($driver) ?: '—' }}</x-panel.badge></td>@endif
                        <td>
                            <div class="p-cell-main">
                                <span class="p-avatar">{{ mb_substr($driver->firstName ?: '؟', 0, 1) }}</span>
                                <div>
                                    <strong>{{ trim($driver->firstName.' '.$driver->lastName) }}</strong>
                                    <span class="p-cell-sub">#{{ $driver->id }} @if($driver->car_owner)· {{ textByLanguage('يملك مركبة', 'Car owner') }}@endif</span>
                                </div>
                            </div>
                        </td>
                        <td dir="ltr" style="text-align:start;">{{ trim(($driver->dialCode ? '+'.ltrim($driver->dialCode,'+').' ' : '').$driver->phoneNumber) }}</td>
                        @if($isAdmin)
                            <td>{{ $officeOptions[$driver->officeId] ?? '—' }}</td>
                        @endif
                        <td>{{ number_format((int) $driver->rideCount) }}</td>
                        <td><i class="bi bi-star-fill" style="color:var(--p-accent);font-size:.8rem;"></i> {{ number_format((float) $driver->rating, 1) }}</td>
                        <td>
                            <x-panel.badge :tone="$driver->isActive ? 'success' : 'danger'">
                                {{ $driver->isActive ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ shardLink($r('driver.show'), $driver->id, $driver) }}" class="p-icon-btn" title="{{ textByLanguage('البطاقة', 'Card') }}">
                                    <i class="bi bi-person-vcard"></i>
                                </a>
                                <a href="{{ shardLink($r('driver.edit'), $driver->id, $driver) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route($r('driver.toggle'), $driver->id) }}">
                                    @csrf
                                    @if(shardOf($driver))<input type="hidden" name="country" value="{{ shardOf($driver) }}">@endif
                                    <button type="submit" class="p-icon-btn" title="{{ $driver->isActive ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}">
                                        <i class="bi {{ $driver->isActive ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route($r('driver.destroy'), $driver->id) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف هذا السائق؟', 'Delete this driver?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    @if(shardOf($driver))<input type="hidden" name="country" value="{{ shardOf($driver) }}">@endif
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($drivers->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $drivers->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $drivers->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $drivers->currentPage() }} / {{ $drivers->lastPage() }}</span>
                    <a class="p-page {{ ! $drivers->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $drivers->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty">
                <i class="bi bi-taxi-front"></i>
                {{ ($search || $officeFilter) ? textByLanguage('لا توجد نتائج مطابقة', 'No matching results') : textByLanguage('لا يوجد سائقون بعد', 'No drivers yet') }}
            </p>
        @endif
    </div>

@endsection
