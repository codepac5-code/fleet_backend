@extends('panel.layouts.master')

@section('title', textByLanguage('طُرُز وألوان المركبات', 'Vehicle models & colours'))
@section('page-title', textByLanguage('طُرُز وألوان المركبات', 'Vehicle models & colours'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $inp = 'width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);';
    $lbl = 'display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;';
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('كتالوج المركبات', 'Vehicle catalog')"
        :subtitle="textByLanguage('الطُرُز والألوان التي تُقترح عند إضافة مركبة — قوائم مشتركة بين كل الدول', 'The models and colours suggested when adding a vehicle — shared across every country')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <h3 style="font-size:.95rem;font-weight:800;margin:0 0 12px;"><i class="bi bi-car-front"></i> {{ textByLanguage('الطُرُز', 'Models') }}</h3>

        <form id="modelForm" method="POST" action="{{ route($r('vehicle-models.store')) }}" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:14px;">
            @csrf
            <input type="hidden" name="_method" id="modelMethod" value="POST">
            <div><label style="{{ $lbl }}">{{ textByLanguage('الماركة', 'Brand') }}</label>
                <select name="brand_id" id="modelBrand" style="{{ $inp }}">
                    <option value="">{{ textByLanguage('بدون ماركة', 'No brand') }}</option>
                    @foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name_en }}</option>@endforeach
                </select></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('بالعربية', 'Arabic') }}</label>
                <input name="name" id="modelAr" required style="{{ $inp }}"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('بالإنجليزية', 'English') }}</label>
                <input name="name_en" id="modelEn" required placeholder="Corolla" style="{{ $inp }}"></div>
            <div style="display:flex; gap:6px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span id="modelSubmitLabel">{{ textByLanguage('إضافة', 'Add') }}</span></button>
                <button type="button" id="modelReset" class="p-btn p-btn--soft" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
            </div>
        </form>

        @if($models->count())
            <x-panel.table :headers="[textByLanguage('الماركة', 'Brand'), textByLanguage('بالعربية', 'Arabic'), textByLanguage('بالإنجليزية', 'English'), textByLanguage('الحالة', 'Status'), '']">
                @foreach($models as $model)
                    <tr>
                        <td>{{ $brandNames[$model->brand_id] ?? '—' }}</td>
                        <td>{{ $model->name }}</td>
                        <td>{{ $model->name_en }}</td>
                        <td><x-panel.badge :tone="$model->status ? 'success' : 'gray'">{{ $model->status ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}</x-panel.badge></td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-model-edit data-id="{{ $model->id }}" data-brand="{{ $model->brand_id }}"
                                    data-ar="{{ $model->name }}" data-en="{{ $model->name_en }}"><i class="bi bi-pencil"></i></button>
                                <form method="POST" action="{{ route($r('vehicle-catalog.toggle'), ['type' => 'model', 'id' => $model->id]) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $model->status ? textByLanguage('تعطيل', 'Deactivate') : textByLanguage('تفعيل', 'Activate') }}">
                                        <i class="bi {{ $model->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-car-front"></i> {{ textByLanguage('لا توجد طُرُز', 'No models') }}</p>
        @endif
    </div>

    <div class="p-card">
        <h3 style="font-size:.95rem;font-weight:800;margin:0 0 12px;"><i class="bi bi-palette"></i> {{ textByLanguage('الألوان', 'Colours') }}</h3>

        <form id="colorForm" method="POST" action="{{ route($r('vehicle-colors.store')) }}" style="display:grid; grid-template-columns:1fr 1fr auto auto; gap:10px; align-items:end; margin-bottom:14px;">
            @csrf
            <input type="hidden" name="_method" id="colorMethod" value="POST">
            <div><label style="{{ $lbl }}">{{ textByLanguage('بالعربية', 'Arabic') }}</label>
                <input name="name" id="colorAr" required style="{{ $inp }}"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('بالإنجليزية', 'English') }}</label>
                <input name="name_en" id="colorEn" required placeholder="White" style="{{ $inp }}"></div>
            <div><label style="{{ $lbl }}">{{ textByLanguage('الرمز', 'Swatch') }}</label>
                <input name="hex" id="colorHex" type="color" value="#ffffff" style="width:56px;height:38px;padding:2px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div style="display:flex; gap:6px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span id="colorSubmitLabel">{{ textByLanguage('إضافة', 'Add') }}</span></button>
                <button type="button" id="colorReset" class="p-btn p-btn--soft" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
            </div>
        </form>

        @if($colors->count())
            <x-panel.table :headers="['', textByLanguage('بالعربية', 'Arabic'), textByLanguage('بالإنجليزية', 'English'), textByLanguage('الحالة', 'Status'), '']">
                @foreach($colors as $color)
                    <tr>
                        <td><span style="display:inline-block;width:18px;height:18px;border-radius:50%;border:1px solid var(--p-border);background:{{ $color->hex ?: 'transparent' }};"></span></td>
                        <td>{{ $color->name }}</td>
                        <td>{{ $color->name_en }}</td>
                        <td><x-panel.badge :tone="$color->status ? 'success' : 'gray'">{{ $color->status ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}</x-panel.badge></td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-color-edit data-id="{{ $color->id }}" data-ar="{{ $color->name }}"
                                    data-en="{{ $color->name_en }}" data-hex="{{ $color->hex }}"><i class="bi bi-pencil"></i></button>
                                <form method="POST" action="{{ route($r('vehicle-catalog.toggle'), ['type' => 'color', 'id' => $color->id]) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $color->status ? textByLanguage('تعطيل', 'Deactivate') : textByLanguage('تفعيل', 'Activate') }}">
                                        <i class="bi {{ $color->status ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-palette"></i> {{ textByLanguage('لا توجد ألوان', 'No colours') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    function wire(prefix, base, fields) {
        var form = document.getElementById(prefix + 'Form');
        if (!form) return;
        var storeAction = form.getAttribute('action');
        var reset = document.getElementById(prefix + 'Reset');

        function clear() {
            form.setAttribute('action', storeAction);
            document.getElementById(prefix + 'Method').value = 'POST';
            Object.keys(fields).forEach(function (id) { document.getElementById(id).value = fields[id]; });
            document.getElementById(prefix + 'SubmitLabel').textContent = @json(textByLanguage('إضافة', 'Add'));
            reset.style.display = 'none';
        }

        document.querySelectorAll('[data-' + prefix + '-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.setAttribute('action', base + '/' + btn.dataset.id);
                document.getElementById(prefix + 'Method').value = 'PUT';
                Object.keys(fields).forEach(function (id) {
                    var key = id.replace(prefix, '').toLowerCase();
                    document.getElementById(id).value = btn.dataset[key] || fields[id];
                });
                document.getElementById(prefix + 'SubmitLabel').textContent = @json(textByLanguage('حفظ', 'Save'));
                reset.style.display = '';
            });
        });

        reset.addEventListener('click', clear);
    }

    wire('model', "{{ url('panel/admin/vehicle-models') }}", {modelBrand: '', modelAr: '', modelEn: ''});
    wire('color', "{{ url('panel/admin/vehicle-colors') }}", {colorAr: '', colorEn: '', colorHex: '#ffffff'});
})();
</script>
@endpush
