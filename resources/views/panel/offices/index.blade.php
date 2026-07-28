@extends('panel.layouts.master')

@section('title', __('messages.offices'))
@section('page-title', __('messages.offices'))

@php
    $agg = \App\Http\Core\GeoServices\ShardAggregator::isActive();
    $rowShard = fn ($m) => $agg ? $m->getAttribute('_shard') : null;
    $link = fn ($route, $id, $m) => route($route, $id) . ($rowShard($m) ? '?country=' . $rowShard($m) : '');
    $officeHeaders = array_values(array_filter([
        $agg ? textByLanguage('الدولة', 'Country') : null,
        textByLanguage('المكتب', 'Office'),
        textByLanguage('التواصل', 'Contact'),
        textByLanguage('الموقع', 'Location'),
        textByLanguage('حد الطلبات', 'Order limit'),
        textByLanguage('الحالة', 'Status'),
        '',
    ], fn ($h) => $h !== null));
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="__('messages.offices')"
        :subtitle="$agg ? textByLanguage('عرض موحّد لكل الدول — التعديل يُطبّق على دولة السجل', 'Unified view across all countries — edits apply to each record’s country') : textByLanguage('إدارة المكاتب المشتركة', 'Manage subscribed offices')">
        <x-slot:actions>
            @if($agg)
                <div class="p-agg-add">
                    <span class="p-btn p-btn--ghost"><i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة مكتب في', 'Add office in') }}</span>
                    @foreach(($panelCountries ?? []) as $c)
                        <a href="{{ route('panel.admin.office.create') }}?country={{ $c->id }}" class="p-btn p-btn--primary" style="margin-inline-start:6px;">{{ $c->country_code ?: $c->name }}</a>
                    @endforeach
                </div>
            @else
                <a href="{{ route('panel.admin.office.create') }}" class="p-btn p-btn--primary">
                    <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة مكتب', 'Add office') }}
                </a>
            @endif
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route('panel.admin.office.index') }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="{{ textByLanguage('ابحث بالاسم أو البريد أو الهاتف', 'Search by name, email or phone') }}">
            @if($search)
                <a href="{{ route('panel.admin.office.index') }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>
            @endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($offices->count())
            <x-panel.table :headers="$officeHeaders">
                @foreach($offices as $office)
                    <tr>
                        @if($agg)
                            <td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ $office->getAttribute('_country') ?: '—' }}</x-panel.badge></td>
                        @endif
                        <td>
                            <div class="p-cell-main">
                                <span class="p-avatar">{{ mb_substr($office->officeName, 0, 1) }}</span>
                                <div>
                                    <strong>{{ $office->officeName }}@if($office->is_verified)<i class="bi bi-patch-check-fill" style="color:var(--p-primary);margin-inline-start:5px;" title="{{ textByLanguage('موثّق', 'Verified') }}"></i>@endif @if($office->is_monitored)<i class="bi bi-eye-fill" style="color:var(--p-warning);margin-inline-start:4px;" title="{{ textByLanguage('تحت المراقبة', 'Monitored') }}"></i>@endif</strong>
                                    <span class="p-cell-sub">#{{ $office->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $office->email }}</div>
                            <span class="p-cell-sub">{{ $office->contactNumber ?: '—' }}</span>
                        </td>
                        <td>{{ collect([$office->city, $office->region, $office->country])->filter()->implode('، ') ?: '—' }}</td>
                        <td>{{ $office->limitOrders ? number_format($office->limitOrders) : textByLanguage('غير محدود', 'Unlimited') }}</td>
                        <td>
                            <x-panel.badge :tone="$office->status ? 'success' : 'danger'">
                                {{ $office->status ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ $link('panel.admin.office.show', $office->id, $office) }}" class="p-icon-btn" title="{{ textByLanguage('التفاصيل', 'Details') }}">
                                    <i class="bi bi-graph-up"></i>
                                </a>
                                <a href="{{ $link('panel.admin.office.permissions.edit', $office->id, $office) }}" class="p-icon-btn" title="{{ textByLanguage('الصلاحيات', 'Permissions') }}">
                                    <i class="bi bi-shield-lock"></i>
                                </a>
                                <a href="{{ $link('panel.admin.office.pricing.edit', $office->id, $office) }}" class="p-icon-btn" title="{{ textByLanguage('التسعير', 'Pricing') }}">
                                    <i class="bi bi-tags"></i>
                                </a>
                                <a href="{{ $link('panel.admin.office.edit', $office->id, $office) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('panel.admin.office.toggle', $office->id) }}">
                                    @csrf
                                    @if($rowShard($office))<input type="hidden" name="country" value="{{ $rowShard($office) }}">@endif
                                    <button type="submit" class="p-icon-btn" title="{{ $office->status ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}">
                                        <i class="bi {{ $office->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('panel.admin.office.destroy', $office->id) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف هذا المكتب؟', 'Delete this office?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    @if($rowShard($office))<input type="hidden" name="country" value="{{ $rowShard($office) }}">@endif
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($offices->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $offices->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $offices->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $offices->currentPage() }} / {{ $offices->lastPage() }}</span>
                    <a class="p-page {{ ! $offices->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $offices->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty">
                <i class="bi bi-building"></i>
                {{ $search ? textByLanguage('لا توجد نتائج مطابقة', 'No matching results') : textByLanguage('لا توجد مكاتب بعد', 'No offices yet') }}
            </p>
        @endif
    </div>

@endsection
