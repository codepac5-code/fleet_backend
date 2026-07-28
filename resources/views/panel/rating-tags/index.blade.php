@extends('panel.layouts.master')

@section('title', textByLanguage('وسوم التقييم', 'Rating tags'))
@section('page-title', textByLanguage('وسوم التقييم', 'Rating tags'))

@php
    $r = fn ($n) => "panel.admin.{$n}";
    $audiences = ['rider', 'driver', 'both'];
    $audienceLabel = ['rider' => textByLanguage('راكب يقيّم سائقاً', 'Rider rates driver'), 'driver' => textByLanguage('سائق يقيّم راكباً', 'Driver rates rider'), 'both' => textByLanguage('كلاهما', 'Both')];
@endphp

@section('content')

    <x-panel.page-toolbar
        :title="textByLanguage('وسوم التقييم', 'Rating tags')"
        :subtitle="textByLanguage('الوسوم التي تظهر في شاشة التقييم بعد الرحلة، حسب عدد النجوم والجمهور', 'The chips shown on the post-trip rating screen, by star count and audience')" />

    @if(session('status'))<div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>@endif
    @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif
    @if($errors->any())<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>@endif

    <div class="p-card" style="margin-bottom:16px;">
        <form id="tagForm" method="POST" action="{{ route($r('rating-tags.store')) }}" style="display:grid; grid-template-columns:1fr 1fr 1fr auto auto auto auto auto; gap:10px; align-items:end;">
            @csrf
            <input type="hidden" name="_method" id="tagMethod" value="POST">
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('الرمز', 'Code') }}</label>
                <input name="code" id="tagCode" required placeholder="clean_car" style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('بالعربية', 'Arabic') }}</label>
                <input name="label_ar" id="tagAr" required style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('بالإنجليزية', 'English') }}</label>
                <input name="label_en" id="tagEn" required style="width:100%;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('الجمهور', 'Audience') }}</label>
                <select name="audience" id="tagAudience" style="padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);">
                    @foreach($audiences as $a)<option value="{{ $a }}">{{ $audienceLabel[$a] }}</option>@endforeach
                </select></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('من نجمة', 'Stars from') }}</label>
                <input name="stars_min" id="tagMin" type="number" value="1" min="1" max="5" style="width:70px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('إلى نجمة', 'Stars to') }}</label>
                <input name="stars_max" id="tagMax" type="number" value="5" min="1" max="5" style="width:70px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div><label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('ترتيب', 'Sort') }}</label>
                <input name="sort" id="tagSort" type="number" value="0" min="0" style="width:70px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"></div>
            <div style="display:flex; gap:6px;">
                <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> <span id="tagSubmitLabel">{{ textByLanguage('إضافة', 'Add') }}</span></button>
                <button type="button" id="tagReset" class="p-btn p-btn--soft" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
            </div>
        </form>
    </div>

    <div class="p-card">
        @if($tags->count())
            <x-panel.table :headers="[textByLanguage('الرمز', 'Code'), textByLanguage('بالعربية', 'Arabic'), textByLanguage('بالإنجليزية', 'English'), textByLanguage('الجمهور', 'Audience'), textByLanguage('النجوم', 'Stars'), textByLanguage('ترتيب', 'Sort'), textByLanguage('الحالة', 'Status'), '']">
                @foreach($tags as $tag)
                    <tr>
                        <td><code>{{ $tag->code }}</code></td>
                        <td>{{ $tag->label_ar }}</td>
                        <td>{{ $tag->label_en }}</td>
                        <td><span class="p-badge p-badge--gray">{{ $audienceLabel[$tag->audience] ?? $tag->audience }}</span></td>
                        <td dir="ltr">{{ $tag->stars_min }}–{{ $tag->stars_max }} <i class="bi bi-star-fill" style="font-size:.7rem;color:#f59e0b;"></i></td>
                        <td>{{ $tag->sort }}</td>
                        <td><x-panel.badge :tone="$tag->is_active ? 'success' : 'gray'">{{ $tag->is_active ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}</x-panel.badge></td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-tag-edit
                                    data-id="{{ $tag->id }}"
                                    data-code="{{ $tag->code }}"
                                    data-ar="{{ $tag->label_ar }}"
                                    data-en="{{ $tag->label_en }}"
                                    data-audience="{{ $tag->audience }}"
                                    data-min="{{ $tag->stars_min }}"
                                    data-max="{{ $tag->stars_max }}"
                                    data-sort="{{ $tag->sort }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route($r('rating-tags.toggle'), $tag->id) }}">
                                    @csrf
                                    <button type="submit" class="p-icon-btn" title="{{ $tag->is_active ? textByLanguage('تعطيل', 'Deactivate') : textByLanguage('تفعيل', 'Activate') }}">
                                        <i class="bi {{ $tag->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-tags"></i> {{ textByLanguage('لا توجد وسوم', 'No tags') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tagForm');
    if (!form) return;
    var storeAction = form.getAttribute('action');
    var updateBase = "{{ url('panel/admin/rating-tags') }}";

    function reset() {
        form.setAttribute('action', storeAction);
        document.getElementById('tagMethod').value = 'POST';
        ['tagCode', 'tagAr', 'tagEn'].forEach(function (id) { document.getElementById(id).value = ''; });
        document.getElementById('tagSort').value = '0';
        document.getElementById('tagMin').value = '1';
        document.getElementById('tagMax').value = '5';
        document.getElementById('tagAudience').value = 'rider';
        document.getElementById('tagSubmitLabel').textContent = @json(textByLanguage('إضافة', 'Add'));
        document.getElementById('tagReset').style.display = 'none';
    }

    document.querySelectorAll('[data-tag-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', updateBase + '/' + btn.dataset.id);
            document.getElementById('tagMethod').value = 'PUT';
            document.getElementById('tagCode').value = btn.dataset.code || '';
            document.getElementById('tagAr').value = btn.dataset.ar || '';
            document.getElementById('tagEn').value = btn.dataset.en || '';
            document.getElementById('tagAudience').value = btn.dataset.audience || 'rider';
            document.getElementById('tagMin').value = btn.dataset.min || '1';
            document.getElementById('tagMax').value = btn.dataset.max || '5';
            document.getElementById('tagSort').value = btn.dataset.sort || '0';
            document.getElementById('tagSubmitLabel').textContent = @json(textByLanguage('حفظ', 'Save'));
            document.getElementById('tagReset').style.display = '';
            document.getElementById('tagCode').focus();
        });
    });

    document.getElementById('tagReset').addEventListener('click', reset);
})();
</script>
@endpush
