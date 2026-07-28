@extends('panel.layouts.master')

@section('title', textByLanguage('أسباب الإلغاء', 'Cancellation reasons'))
@section('page-title', textByLanguage('أسباب الإلغاء', 'Cancellation reasons'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $audiences = ['rider', 'driver', 'both'];
    $audienceLabel = ['rider' => textByLanguage('راكب', 'Rider'), 'driver' => textByLanguage('سائق', 'Driver'), 'both' => textByLanguage('كلاهما', 'Both')];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('أسباب الإلغاء', 'Cancellation reasons')"
        :subtitle="textByLanguage('قائمة أسباب الإلغاء التي يختار منها الراكب والسائق في هذه الدولة', 'The cancellation picklist riders and drivers choose from in this country')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form id="reasonForm" method="POST" action="{{ route($r('cancellation-reasons.store')) }}" style="display:grid; grid-template-columns:1fr 1fr 1fr auto auto auto; gap:10px; align-items:end;">
            @csrf
            <input type="hidden" name="_method" id="reasonMethod" value="POST">
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('الرمز', 'Code') }}</label>
                <input name="code" id="reasonCode" required placeholder="driver_late" style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('بالعربية', 'Arabic') }}</label>
                <input name="label_ar" id="reasonAr" required style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('بالإنجليزية', 'English') }}</label>
                <input name="label_en" id="reasonEn" required style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('الجمهور', 'Audience') }}</label>
                <select name="audience" id="reasonAudience" style="padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                    @foreach($audiences as $a)<option value="{{ $a }}">{{ $audienceLabel[$a] }}</option>@endforeach
                </select></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('ترتيب', 'Sort') }}</label>
                <input name="sort" id="reasonSort" type="number" value="0" min="0" style="width:70px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div style="display:flex; gap:6px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span id="reasonSubmitLabel">{{ textByLanguage('إضافة', 'Add') }}</span></button>
                <button type="button" id="reasonReset" class="p-btn p-btn--soft" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
            </div>
        </form>
    </div>

    <div class="p-card">
        @if($reasons->count())
            <x-panel.table :headers="[textByLanguage('الرمز', 'Code'), textByLanguage('بالعربية', 'Arabic'), textByLanguage('بالإنجليزية', 'English'), textByLanguage('الجمهور', 'Audience'), textByLanguage('ترتيب', 'Sort'), textByLanguage('الحالة', 'Status'), '']">
                @foreach($reasons as $reason)
                    <tr>
                        <td><code>{{ $reason->code }}</code></td>
                        <td>{{ $reason->label_ar }}</td>
                        <td>{{ $reason->label_en }}</td>
                        <td><span class="p-badge p-badge--gray">{{ $audienceLabel[$reason->audience] ?? $reason->audience }}</span></td>
                        <td>{{ $reason->sort }}</td>
                        <td><x-panel.badge :tone="$reason->is_active ? 'success' : 'gray'">{{ $reason->is_active ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}</x-panel.badge></td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-reason-edit
                                    data-id="{{ $reason->id }}"
                                    data-code="{{ $reason->code }}"
                                    data-ar="{{ $reason->label_ar }}"
                                    data-en="{{ $reason->label_en }}"
                                    data-audience="{{ $reason->audience }}"
                                    data-sort="{{ $reason->sort }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route($r('cancellation-reasons.toggle'), $reason->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $reason->is_active ? textByLanguage('تعطيل', 'Deactivate') : textByLanguage('تفعيل', 'Activate') }}">
                                        <i class="bi {{ $reason->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-x-circle"></i> {{ textByLanguage('لا توجد أسباب', 'No reasons') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('reasonForm');
    if (!form) return;
    var storeAction = form.getAttribute('action');
    var updateBase = "{{ url('panel/admin/cancellation-reasons') }}";

    function reset() {
        form.setAttribute('action', storeAction);
        document.getElementById('reasonMethod').value = 'POST';
        ['reasonCode', 'reasonAr', 'reasonEn'].forEach(function (id) { document.getElementById(id).value = ''; });
        document.getElementById('reasonSort').value = '0';
        document.getElementById('reasonAudience').value = 'rider';
        document.getElementById('reasonSubmitLabel').textContent = @json(textByLanguage('إضافة', 'Add'));
        document.getElementById('reasonReset').style.display = 'none';
    }

    document.querySelectorAll('[data-reason-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', updateBase + '/' + btn.dataset.id);
            document.getElementById('reasonMethod').value = 'PUT';
            document.getElementById('reasonCode').value = btn.dataset.code || '';
            document.getElementById('reasonAr').value = btn.dataset.ar || '';
            document.getElementById('reasonEn').value = btn.dataset.en || '';
            document.getElementById('reasonAudience').value = btn.dataset.audience || 'rider';
            document.getElementById('reasonSort').value = btn.dataset.sort || '0';
            document.getElementById('reasonSubmitLabel').textContent = @json(textByLanguage('حفظ', 'Save'));
            document.getElementById('reasonReset').style.display = '';
            document.getElementById('reasonCode').focus();
        });
    });

    document.getElementById('reasonReset').addEventListener('click', reset);
})();
</script>
@endpush
