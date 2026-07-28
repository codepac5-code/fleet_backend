@extends('panel.layouts.master')

@section('title', textByLanguage('ماركات المركبات', 'Vehicle brands'))
@section('page-title', textByLanguage('ماركات المركبات', 'Vehicle brands'))

@php $r = fn ($n) => "panel.admin.{$n}"; @endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('كتالوج ماركات المركبات', 'Vehicle brand catalog')"
        :subtitle="textByLanguage('قائمة مرجعية عامة للماركات — تُقرأ عند تسجيل مركبات السائقين', 'A global reference list of makes — read when registering driver vehicles')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form id="brandForm" method="POST" action="{{ route($r('vehicle-brands.store')) }}" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:12px; align-items:end;">
            @csrf
            <input type="hidden" name="_method" id="brandMethod" value="POST">
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('الاسم', 'Name') }}</label>
                <input name="name" id="brandName" required style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('الاسم بالإنجليزية', 'Latin name') }}</label>
                <input name="name_en" id="brandNameEn" required style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:5px;">{{ textByLanguage('رابط الصورة (اختياري)', 'Image URL (optional)') }}</label>
                <input name="image" id="brandImage" style="width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span id="brandSubmitLabel">{{ textByLanguage('إضافة', 'Add') }}</span></button>
                <button type="button" id="brandReset" class="p-btn p-btn--soft" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
            </div>
        </form>
    </div>

    <div class="p-card">
        @if($brands->count())
            <x-panel.table :headers="['#', textByLanguage('الاسم', 'Name'), textByLanguage('الإنجليزية', 'Latin'), textByLanguage('الحالة', 'Status'), '']">
                @foreach($brands as $b)
                    <tr>
                        <td>{{ $b->id }}</td>
                        <td><strong>{{ $b->name }}</strong></td>
                        <td>{{ $b->name_en ?: '—' }}</td>
                        <td>
                            <x-panel.badge :tone="$b->status ? 'success' : 'gray'">
                                {{ $b->status ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Inactive') }}
                            </x-panel.badge>
                        </td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-brand-edit
                                    data-id="{{ $b->id }}"
                                    data-name="{{ $b->name }}"
                                    data-name-en="{{ $b->name_en }}"
                                    data-image="{{ $b->image }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route($r('vehicle-brands.toggle'), $b->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $b->status ? textByLanguage('تعطيل', 'Deactivate') : textByLanguage('تفعيل', 'Activate') }}">
                                        <i class="bi {{ $b->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-car-front"></i> {{ textByLanguage('لا توجد ماركات', 'No brands') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('brandForm');
    if (!form) return;
    var storeAction = form.getAttribute('action');
    var updateBase = "{{ url('panel/admin/vehicle-brands') }}";

    function resetForm() {
        form.setAttribute('action', storeAction);
        document.getElementById('brandMethod').value = 'POST';
        document.getElementById('brandName').value = '';
        document.getElementById('brandNameEn').value = '';
        document.getElementById('brandImage').value = '';
        document.getElementById('brandSubmitLabel').textContent = @json(textByLanguage('إضافة', 'Add'));
        document.getElementById('brandReset').style.display = 'none';
    }

    document.querySelectorAll('[data-brand-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', updateBase + '/' + btn.dataset.id);
            document.getElementById('brandMethod').value = 'PUT';
            document.getElementById('brandName').value = btn.dataset.name || '';
            document.getElementById('brandNameEn').value = btn.dataset.nameEn || '';
            document.getElementById('brandImage').value = btn.dataset.image || '';
            document.getElementById('brandSubmitLabel').textContent = @json(textByLanguage('حفظ', 'Save'));
            document.getElementById('brandReset').style.display = '';
            document.getElementById('brandName').focus();
        });
    });

    document.getElementById('brandReset').addEventListener('click', resetForm);
})();
</script>
@endpush
