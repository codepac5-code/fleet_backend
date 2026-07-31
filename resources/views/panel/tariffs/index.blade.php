@extends('panel.layouts.master')

@section('title', textByLanguage('التعرفات', 'Tariffs'))
@section('page-title', textByLanguage('التعرفات', 'Tariffs'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $money = fn ($m) => number_format(((int) $m) / 100, 2);
    $groups = $serviceClasses ?? [];
    $co = $corridors ?? ['count' => 0, 'min' => null, 'max' => null];
@endphp

@push('styles')
<style>
    .tf-lead{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;margin-bottom:18px;}
    .tf-lead__card{background:var(--p-surface,#fff);border:1px solid var(--p-border);border-radius:14px;padding:14px 16px;}
    .tf-lead__top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;}
    .tf-lead__title{margin:0;font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px;}
    .tf-lead__num{font-size:1.6rem;font-weight:800;line-height:1.1;}
    .tf-lead__note{font-size:.8rem;color:var(--p-muted,#6b7280);margin:6px 0 0;}
    .tf-lead__warn{font-size:.83rem;font-weight:600;color:var(--p-danger);margin:0;}

    .tf-form{display:grid;gap:16px;}
    .tf-row{display:grid;grid-template-columns:minmax(240px,1.2fr) minmax(240px,1fr);gap:16px;align-items:start;}
    .tf-label{display:block;font-size:.82rem;font-weight:700;margin-bottom:6px;}
    .tf-hint{font-size:.78rem;color:var(--p-muted,#6b7280);margin:6px 0 0;min-height:1.1em;}
    .tf-hint--warn{color:var(--p-danger);font-weight:600;}

    .tf-styles{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    .tf-style{position:relative;border:1.5px solid var(--p-border);border-radius:12px;padding:10px 12px;cursor:pointer;display:flex;gap:10px;align-items:flex-start;transition:border-color .15s,background .15s;}
    .tf-style input{position:absolute;opacity:0;pointer-events:none;}
    .tf-style i{font-size:1.15rem;line-height:1.2;}
    .tf-style b{display:block;font-size:.85rem;}
    .tf-style span{display:block;font-size:.75rem;color:var(--p-muted,#6b7280);}
    .tf-style.is-on{border-color:var(--p-primary);background:rgba(49,40,115,.06);}

    .tf-fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;}
    .tf-money{position:relative;}
    .tf-money input{width:100%;padding-inline-end:52px;}
    .tf-money__cur{position:absolute;inset-inline-end:10px;top:50%;transform:translateY(-50%);font-size:.72rem;font-weight:700;color:var(--p-muted,#6b7280);pointer-events:none;}
    .tf-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
    .tf-editing{display:none;font-size:.82rem;font-weight:600;color:var(--p-primary);}
    .tf-editing.is-on{display:inline-flex;align-items:center;gap:6px;}
    @media(max-width:820px){.tf-row{grid-template-columns:1fr;}.tf-styles{grid-template-columns:1fr;}}
</style>
@endpush

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('تعرفات مكتبي', 'My tariffs')"
        :subtitle="textByLanguage('تجاوز اختياري بالفئة — السعر الأساسي من خدماتي، والسفر من أسعار الخطوط', 'Optional per-class override — the base price comes from My services, travel from Fixed corridors')" />

    <div class="tf-lead">
        <div class="tf-lead__card">
            <div class="tf-lead__top">
                <h3 class="tf-lead__title"><i class="bi bi-grid-1x2"></i> {{ textByLanguage('خدمات مكتبي', 'My services') }}</h3>
                @if(\Illuminate\Support\Facades\Route::has($r('services.mine')))
                    <a href="{{ route($r('services.mine')) }}" class="p-btn p-btn--soft"><i class="bi bi-sliders"></i> {{ textByLanguage('إدارة', 'Manage') }}</a>
                @endif
            </div>

            @if(($offered ?? 0) > 0)
                <p class="tf-lead__num">{{ $offered }}</p>
                <p class="tf-lead__note">{{ textByLanguage('خدمة فرعية مفعّلة — وهذه هي التي يبحث عنها الراكب، ومنها يأتي سعر الرحلة العادية.', 'enabled sub-services — this is what riders search, and where a normal ride price comes from.') }}</p>
            @else
                <p class="tf-lead__warn"><i class="bi bi-exclamation-triangle"></i> {{ textByLanguage('لا خدمات مفعّلة — لن يظهر مكتبك لأي راكب مهما كانت التعرفات هنا.', 'No services enabled — no rider sees your office, whatever the tariffs below say.') }}</p>
            @endif
        </div>

        <div class="tf-lead__card">
            <div class="tf-lead__top">
                <h3 class="tf-lead__title"><i class="bi bi-signpost-split"></i> {{ textByLanguage('خطوط السفر', 'Travel corridors') }}</h3>
                @if(\Illuminate\Support\Facades\Route::has($r('pricing.corridors.index')))
                    <a href="{{ route($r('pricing.corridors.index')) }}" class="p-btn p-btn--soft"><i class="bi bi-sliders"></i> {{ textByLanguage('أسعار الخطوط', 'Corridors') }}</a>
                @endif
            </div>

            @if($co['count'] > 0)
                <p class="tf-lead__num">{{ $co['count'] }}</p>
                <p class="tf-lead__note">
                    {{ textByLanguage('خط سفر مسعَّر', 'priced corridors') }}
                    @if($co['min'] !== null)
                        ({{ number_format((float) $co['min'], 2) }}@if((float) $co['max'] > (float) $co['min']) – {{ number_format((float) $co['max'], 2) }}@endif {{ $currency }})
                    @endif
                    — {{ textByLanguage('رحلة السفر سعرها سعر الخط نفسه، لا التعرفة.', 'a travel trip is charged the corridor price, not the tariff.') }}
                </p>
            @else
                <p class="tf-lead__warn"><i class="bi bi-exclamation-triangle"></i> {{ textByLanguage('لا خطوط منشورة — لن يُعرض مكتبك على أي راكب يطلب رحلة سفر.', 'No corridors published — your office is offered to nobody who asks for a Travel trip.') }}</p>
            @endif
        </div>
    </div>

    <x-panel.card :title="textByLanguage('إضافة / تعديل تعرفة', 'Add / edit tariff')">
        <form method="POST" action="{{ route($r('tariffs.save')) }}" class="tf-form" id="tf-form">
            @csrf

            <div class="tf-row">
                <div>
                    <label class="tf-label" for="tf-class">{{ textByLanguage('فئة الخدمة', 'Service class') }}</label>
                    <select name="service_class" id="tf-class" class="p-input" required>
                        <option value="" disabled selected>{{ textByLanguage('اختر فئة…', 'Pick a class…') }}</option>
                        @foreach($groups as $groupTitle => $options)
                            <optgroup label="{{ $groupTitle }}">
                                @foreach($options as $opt)
                                    <option value="{{ $opt['value'] }}"
                                            data-travel="{{ $opt['travel'] ? 1 : 0 }}"
                                            data-offered="{{ $opt['offered'] ? 1 : 0 }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    {{-- The class a booking carries IS the sub-service name, so the
                         list is the country's own catalog. It used to be six
                         invented words that matched no sub-service at all. --}}
                    <p class="tf-hint" id="tf-class-hint">{{ textByLanguage('الفئات هي خدماتك الفرعية في هذه الدولة.', 'The classes are your sub-services in this country.') }}</p>
                </div>

                <div>
                    <span class="tf-label">{{ textByLanguage('النمط', 'Style') }}</span>
                    <div class="tf-styles">
                        <label class="tf-style is-on" data-style="meter">
                            <input type="radio" name="pricing_style" value="meter" checked>
                            <i class="bi bi-speedometer2"></i>
                            <span>
                                <b>{{ textByLanguage('عدّاد', 'Meter') }}</b>
                                <span>{{ textByLanguage('يُحتسب بالمسافة والزمن', 'Charged by distance and time') }}</span>
                            </span>
                        </label>
                        <label class="tf-style" data-style="fixed">
                            <input type="radio" name="pricing_style" value="fixed">
                            <i class="bi bi-tag"></i>
                            <span>
                                <b>{{ textByLanguage('مبلغ مقطوع', 'Flat') }}</b>
                                <span>{{ textByLanguage('أجرة واحدة مهما كانت المسافة', 'One fare regardless of distance') }}</span>
                            </span>
                        </label>
                    </div>
                    <p class="tf-hint">{{ textByLanguage('«مقطوع» هنا ليس تسعير السفر — السفر يُسعَّر لكل خط (مدينة ← مدينة).', '“Flat” here is not travel pricing — travel is priced per corridor (city → city).') }}</p>
                </div>
            </div>

            {{-- Amounts are entered in WHOLE CURRENCY (e.g. 8000.00), not minor
                 units. The labels used to read just "Base" / "Per km" while the
                 fields were named `*_minor` and stored as hundredths, so an
                 office that meant 8000 got 80.00 — every price 100x too small.
                 The unit is now stated on every field and converted on save. --}}
            <div class="tf-fields" data-for="meter">
                <div>
                    <label class="tf-label" for="tf-base">{{ textByLanguage('الأساس', 'Base') }}</label>
                    <div class="tf-money"><input type="number" id="tf-base" name="base_amount" min="0" step="0.01" value="0" class="p-input"><span class="tf-money__cur">{{ $currency }}</span></div>
                </div>
                <div>
                    <label class="tf-label" for="tf-km">{{ textByLanguage('لكل كم', 'Per km') }}</label>
                    <div class="tf-money"><input type="number" id="tf-km" name="per_km_amount" min="0" step="0.01" value="0" class="p-input"><span class="tf-money__cur">{{ $currency }}</span></div>
                </div>
                <div>
                    <label class="tf-label" for="tf-min">{{ textByLanguage('لكل دقيقة', 'Per min') }}</label>
                    <div class="tf-money"><input type="number" id="tf-min" name="per_minute_amount" min="0" step="0.01" value="0" class="p-input"><span class="tf-money__cur">{{ $currency }}</span></div>
                </div>
                <div>
                    <label class="tf-label" for="tf-floor">{{ textByLanguage('الحدّ الأدنى', 'Minimum') }}</label>
                    <div class="tf-money"><input type="number" id="tf-floor" name="minimum_amount" min="0" step="0.01" value="0" class="p-input"><span class="tf-money__cur">{{ $currency }}</span></div>
                </div>
            </div>

            <div class="tf-fields" data-for="fixed" style="display:none;">
                <div>
                    <label class="tf-label" for="tf-fixed">{{ textByLanguage('المبلغ المقطوع', 'Flat amount') }}</label>
                    <div class="tf-money"><input type="number" id="tf-fixed" name="fixed_amount" min="0" step="0.01" value="0" class="p-input"><span class="tf-money__cur">{{ $currency }}</span></div>
                </div>
            </div>

            <div class="tf-actions">
                <label class="p-check" style="margin:0;display:flex;align-items:center;gap:8px;font-size:.85rem;">
                    {{-- Without the 0, an unticked box posts nothing and the
                         controller's default (true) silently re-activates it. --}}
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="tf-active" checked>
                    {{ textByLanguage('مفعّلة', 'Active') }}
                </label>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span class="tf-editing" id="tf-editing"><i class="bi bi-pencil"></i> <span id="tf-editing-name"></span></span>
                    <button type="button" class="p-btn p-btn--ghost" id="tf-reset" style="display:none;">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
                    <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-save"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
                </div>
            </div>

            <p class="tf-hint">{{ textByLanguage('الحفظ يستبدل تعرفة الفئة نفسها.', 'Saving replaces the tariff of the same class.') }}</p>
        </form>
    </x-panel.card>

    <div class="p-card">
        @if(count($tariffs))
            <x-panel.table :headers="array_filter([
                shardIsAll() ? textByLanguage('الدولة', 'Country') : null,
                textByLanguage('الفئة', 'Class'),
                textByLanguage('النمط', 'Style'),
                textByLanguage('الأساس', 'Base'),
                textByLanguage('كم/دقيقة', 'Km/Min'),
                textByLanguage('الأدنى', 'Min'),
                textByLanguage('الثابت', 'Fixed'),
                textByLanguage('الحالة', 'Status'),
                '',
            ], fn($h) => $h !== null)">
                @foreach($tariffs as $t)
                    <tr>
                        @if(shardIsAll())<td><x-panel.badge tone="primary"><i class="bi bi-globe2"></i> {{ shardCountry($t) ?: '—' }}</x-panel.badge></td>@endif
                        <td><strong>{{ $t['service_class'] }}</strong></td>
                        <td>
                            <x-panel.badge :tone="$t['pricing_style'] === 'fixed' ? 'warning' : 'primary'">
                                {{ $t['pricing_style'] === 'fixed' ? textByLanguage('مقطوع', 'Flat') : textByLanguage('عدّاد', 'Meter') }}
                            </x-panel.badge>
                        </td>
                        <td>{{ $money($t['base_minor']) }}</td>
                        <td>{{ $money($t['per_km_minor']) }} / {{ $money($t['per_minute_minor']) }}</td>
                        <td>{{ $money($t['minimum_minor']) }}</td>
                        <td>{{ $money($t['fixed_minor']) }}</td>
                        <td><x-panel.badge :tone="$t['is_active'] ? 'success' : 'danger'">{{ $t['is_active'] ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Off') }}</x-panel.badge></td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button type="button" class="p-icon-btn tf-edit"
                                    title="{{ textByLanguage('تعديل', 'Edit') }}"
                                    data-tariff="{{ json_encode($t, JSON_UNESCAPED_UNICODE) }}"><i class="bi bi-pencil"></i></button>
                                <form method="POST" action="{{ route($r('tariffs.delete'), $t['service_class']) }}"
                                    onsubmit="return confirm('{{ textByLanguage('حذف تعرفة هذه الفئة؟', 'Delete this class tariff?') }}');">
                                    @csrf @method('DELETE')
                                    @if(shardOf($t))<input type="hidden" name="country" value="{{ shardOf($t) }}">@endif
                                    <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
        @else
            <p class="p-empty"><i class="bi bi-cash-coin"></i> {{ textByLanguage('لا توجد تعرفات بعد', 'No tariffs yet') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tf-form');
    if (!form) return;

    var classSelect = document.getElementById('tf-class');
    var classHint = document.getElementById('tf-class-hint');
    var editing = document.getElementById('tf-editing');
    var editingName = document.getElementById('tf-editing-name');
    var resetBtn = document.getElementById('tf-reset');
    var styles = form.querySelectorAll('.tf-style');
    var groups = form.querySelectorAll('.tf-fields');

    var T = {
        base: @json(textByLanguage('الفئات هي خدماتك الفرعية في هذه الدولة.', 'The classes are your sub-services in this country.')),
        notOffered: @json(textByLanguage('مكتبك لا يقدّم هذه الخدمة — فعّلها من «خدماتي» وإلا لن تصلك طلبات عليها.', 'Your office does not offer this service — enable it in My services or no request will reach you.')),
        travel: @json(textByLanguage('هذه خدمة سفر: الراكب يدفع سعر الخط من «أسعار الخطوط»، لا هذه التعرفة.', 'This is a travel service: the rider pays the corridor price from Fixed corridors, not this tariff.')),
        editing: @json(textByLanguage('تعديل', 'Editing'))
    };

    function applyStyle(value) {
        styles.forEach(function (s) { s.classList.toggle('is-on', s.dataset.style === value); });
        groups.forEach(function (g) { g.style.display = g.dataset.for === value ? '' : 'none'; });
    }

    function describeClass() {
        var opt = classSelect.options[classSelect.selectedIndex];
        if (!opt || !opt.value) { classHint.textContent = T.base; classHint.classList.remove('tf-hint--warn'); return; }
        if (opt.dataset.travel === '1') { classHint.textContent = T.travel; classHint.classList.add('tf-hint--warn'); return; }
        if (opt.dataset.offered === '0') { classHint.textContent = T.notOffered; classHint.classList.add('tf-hint--warn'); return; }
        classHint.textContent = T.base;
        classHint.classList.remove('tf-hint--warn');
    }

    styles.forEach(function (s) {
        s.addEventListener('click', function () {
            s.querySelector('input').checked = true;
            applyStyle(s.dataset.style);
        });
    });

    classSelect.addEventListener('change', describeClass);

    function money(minor) { return (Number(minor || 0) / 100).toFixed(2); }

    document.querySelectorAll('.tf-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = JSON.parse(btn.dataset.tariff);
            var matched = false;
            Array.prototype.forEach.call(classSelect.options, function (o) {
                if (o.value === t.service_class) { o.selected = true; matched = true; }
            });
            if (!matched) {
                var extra = document.createElement('option');
                extra.value = t.service_class;
                extra.textContent = t.service_class;
                extra.selected = true;
                classSelect.appendChild(extra);
            }
            applyStyle(t.pricing_style === 'fixed' ? 'fixed' : 'meter');
            form.querySelector('input[name="pricing_style"][value="' + (t.pricing_style === 'fixed' ? 'fixed' : 'meter') + '"]').checked = true;
            document.getElementById('tf-base').value = money(t.base_minor);
            document.getElementById('tf-km').value = money(t.per_km_minor);
            document.getElementById('tf-min').value = money(t.per_minute_minor);
            document.getElementById('tf-floor').value = money(t.minimum_minor);
            document.getElementById('tf-fixed').value = money(t.fixed_minor);
            document.getElementById('tf-active').checked = !!t.is_active;
            editingName.textContent = T.editing + ': ' + t.service_class;
            editing.classList.add('is-on');
            resetBtn.style.display = '';
            describeClass();
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    resetBtn.addEventListener('click', function () {
        form.reset();
        applyStyle('meter');
        editing.classList.remove('is-on');
        resetBtn.style.display = 'none';
        describeClass();
    });

    describeClass();
})();
</script>
@endpush
