@extends('panel.layouts.master')

@section('title', textByLanguage('الخدمات الفرعية', 'Sub-services'))
@section('page-title', textByLanguage('الخدمات الفرعية', 'Sub-services'))

@php $title = app()->getLocale() === 'ar' ? ($service->title ?: $service->title_en) : ($service->title_en ?: $service->title); @endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="$title"
        :subtitle="textByLanguage('الخدمات الفرعية وأسعارها الأساسية', 'Sub-services and their base prices')">
        <x-slot:actions>
            <a href="{{ route('panel.admin.service.index') }}" class="p-btn p-btn--ghost">
                <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('الخدمات', 'Services') }}
            </a>
            <a href="{{ route('panel.admin.service.sub.create', $service->id) }}" class="p-btn p-btn--primary">
                <i class="bi bi-plus-lg"></i> {{ textByLanguage('إضافة خدمة فرعية', 'Add sub-service') }}
            </a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="p-card">
        @if($subServices->count())
            <x-panel.table :headers="[
                textByLanguage('الخدمة الفرعية', 'Sub-service'),
                textByLanguage('سعر الفتح', 'Open price'),
                textByLanguage('سعر الكيلومتر', 'Per km'),
                textByLanguage('سعر الدقيقة', 'Per minute'),
                textByLanguage('الحالة', 'Status'),
                '',
            ]">
                @foreach($subServices as $sub)
                    @php $name = app()->getLocale() === 'ar' ? ($sub->name ?: $sub->name_en) : ($sub->name_en ?: $sub->name); @endphp
                    <tr>
                        <td>
                            <div class="p-cell-main">
                                @if($sub->image)
                                    <span class="p-avatar p-avatar--img"><img src="{{ asset('storage/'.$sub->image) }}" alt=""></span>
                                @else
                                    <span class="p-avatar"><i class="bi bi-diagram-3"></i></span>
                                @endif
                                <div>
                                    <strong>{{ $name }}</strong>
                                    <span class="p-cell-sub">#{{ $sub->id }} @if($sub->is_travel)· {{ textByLanguage('سفر', 'Travel') }}@endif</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ getPriceFormat($sub->openPrice) }}</td>
                        <td>{{ getPriceFormat($sub->kmPrice) }}</td>
                        <td>{{ getPriceFormat($sub->minutePrice) }}</td>
                        <td>
                            <x-panel.badge :tone="$sub->status ? 'success' : 'danger'">
                                {{ $sub->status ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <a href="{{ route('panel.admin.service.sub.edit', [$service->id, $sub->id]) }}" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('panel.admin.service.sub.toggle', [$service->id, $sub->id]) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $sub->status ? textByLanguage('تعطيل', 'Disable') : textByLanguage('تفعيل', 'Enable') }}">
                                        <i class="bi {{ $sub->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('panel.admin.service.sub.destroy', [$service->id, $sub->id]) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف هذه الخدمة الفرعية؟', 'Delete this sub-service?') }}');">
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
        @else
            <p class="p-empty"><i class="bi bi-diagram-3"></i> {{ textByLanguage('لا توجد خدمات فرعية بعد', 'No sub-services yet') }}</p>
        @endif
    </div>

@endsection
