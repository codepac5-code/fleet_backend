@extends('panel.layouts.master')

@section('title', textByLanguage('أنواع المستندات', 'Document types'))
@section('page-title', textByLanguage('أنواع المستندات', 'Document types'))

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar :title="textByLanguage('أنواع المستندات', 'Document types')"
        :subtitle="textByLanguage('المستندات المطلوبة من السائقين', 'Documents required from drivers')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.document.create') }}" class="p-btn p-btn--primary">
                <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة نوع', 'Add type') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        <form method="GET" action="{{ route('panel.admin.document.index') }}" class="p-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="{{ $search }}" placeholder="{{ textByLanguage('ابحث باسم المستند', 'Search by name') }}">
            @if($search)<a href="{{ route('panel.admin.document.index') }}" class="p-search__clear">{{ textByLanguage('مسح', 'Clear') }}</a>@endif
            <button type="submit" class="p-btn p-btn--ghost">{{ textByLanguage('بحث', 'Search') }}</button>
        </form>

        @if($documents->count())
            <x-panel.table :headers="[
                textByLanguage('المستند', 'Document'),
                textByLanguage('الإلزامية', 'Requirement'),
                textByLanguage('الحالة', 'Status'),
                '',
            ]">
                @foreach($documents as $doc)
                    <tr>
                        <td>
                            <div class="p-cell-main">
                                <span class="p-avatar"><i class="bi bi-file-earmark-text"></i></span>
                                <div><strong>{{ $doc->name }}</strong><span class="p-cell-sub">#{{ $doc->id }}</span></div>
                            </div>
                        </td>
                        <td>
                            <x-panel.badge :tone="$doc->is_required ? 'warning' : 'gray'">
                                {{ $doc->is_required ? textByLanguage('إلزامي', 'Required') : textByLanguage('اختياري', 'Optional') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <x-panel.badge :tone="$doc->status ? 'success' : 'danger'">
                                {{ $doc->status ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <form method="POST" action="{{ route('panel.admin.document.toggle-required', $doc->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $doc->is_required ? textByLanguage('جعله اختيارياً', 'Make optional') : textByLanguage('جعله إلزامياً', 'Make required') }}">
                                        <i class="bi {{ $doc->is_required ? 'bi-asterisk' : 'bi-dash-circle' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('panel.admin.document.edit', $doc->id) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('panel.admin.document.toggle', $doc->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $doc->status ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}">
                                        <i class="bi {{ $doc->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('panel.admin.document.destroy', $doc->id) }}" onsubmit="return confirm('{{ textByLanguage('حذف هذا النوع؟', 'Delete this type?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>

            @if($documents->hasPages())
                <div class="p-pagination">
                    <a class="p-page {{ $documents->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $documents->previousPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="p-page-info">{{ $documents->currentPage() }} / {{ $documents->lastPage() }}</span>
                    <a class="p-page {{ ! $documents->hasMorePages() ? 'is-disabled' : '' }}" href="{{ $documents->nextPageUrl() ?: '#' }}"><i class="bi bi-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            @endif
        @else
            <p class="p-empty"><i class="bi bi-file-earmark-text"></i> {{ $search ? textByLanguage('لا توجد نتائج', 'No results') : textByLanguage('لا توجد أنواع مستندات بعد', 'No document types yet') }}</p>
        @endif
    </div>

@endsection
