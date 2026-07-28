@extends('panel.layouts.master')

@section('title', textByLanguage('الخدمات', 'Services'))
@section('page-title', textByLanguage('الخدمات', 'Services'))

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="textByLanguage('الخدمات', 'Services')"
        :subtitle="textByLanguage('إدارة الخدمات والخدمات الفرعية', 'Manage services and sub-services')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.service.create') }}" class="p-btn p-btn--primary">
                <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة خدمة', 'Add service') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route('panel.admin.service.index') }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="{{ textByLanguage('ابحث باسم الخدمة', 'Search by service name') }}">
            @if($search)<a href="{{ route('panel.admin.service.index') }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($services->count())
            <x-panel.table :headers="[
                textByLanguage('الخدمة', 'Service'),
                textByLanguage('الخدمات الفرعية', 'Sub-services'),
                textByLanguage('سفر', 'Travel'),
                textByLanguage('الحالة', 'Status'),
                '',
            ]">
                @foreach($services as $service)
                    @php $title = app()->getLocale() === 'ar' ? ($service->title ?: $service->title_en) : ($service->title_en ?: $service->title); @endphp
                    <tr>
                        <td>
                            <div class="p-cell-main">
                                @if($service->image)
                                    <span class="p-avatar p-avatar--img"><img src="{{ asset('storage/'.$service->image) }}" alt=""></span>
                                @else
                                    <span class="p-avatar"><i class="bi bi-grid-1x2"></i></span>
                                @endif
                                <div>
                                    <strong>{{ $title }}</strong>
                                    <span class="p-cell-sub">#{{ $service->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('panel.admin.service.sub.index', $service->id) }}" class="p-link-count">
                                <i class="bi bi-diagram-3"></i> {{ number_format($service->sub_services_count) }}
                            </a>
                        </td>
                        <td>{!! $service->travel_service ? '<i class="bi bi-check-circle" style="color:var(--p-success)"></i>' : '<span style="color:var(--p-text-muted)">—</span>' !!}</td>
                        <td>
                            <x-panel.badge :tone="$service->status ? 'success' : 'danger'">
                                {{ $service->status ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ route('panel.admin.service.show', $service->id) }}" class="p-icon-btn" title="{{ textByLanguage('عرض التفاصيل', 'View details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('panel.admin.service.sub.index', $service->id) }}" class="p-icon-btn" title="{{ textByLanguage('الخدمات الفرعية', 'Sub-services') }}">
                                    <i class="bi bi-diagram-3"></i>
                                </a>
                                <a href="{{ route('panel.admin.service.edit', $service->id) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('panel.admin.service.toggle', $service->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $service->status ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}">
                                        <i class="bi {{ $service->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('panel.admin.service.destroy', $service->id) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف هذه الخدمة وكل خدماتها الفرعية؟', 'Delete this service and its sub-services?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($services->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $services->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $services->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $services->currentPage() }} / {{ $services->lastPage() }}</span>
                    <a class="p-page {{ ! $services->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $services->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty"><i class="bi bi-grid-1x2"></i> {{ $search ? textByLanguage('لا توجد نتائج', 'No results') : textByLanguage('لا توجد خدمات بعد', 'No services yet') }}</p>
        @endif
    </div>

@endsection
