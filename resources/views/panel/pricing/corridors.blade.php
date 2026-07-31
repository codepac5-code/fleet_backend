@extends('panel.layouts.master')

@section('title', textByLanguage('أسعار الخطوط', 'Fixed corridors'))
@section('page-title', textByLanguage('أسعار الخطوط', 'Fixed corridors'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $isAr = app()->getLocale() === 'ar';
    $arrow = $isAr ? '←' : '→';
    $inp = 'width:100%;padding:9px 11px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);background:var(--p-surface);color:var(--p-text);font-family:inherit;font-size:.88rem;';
    $lbl = 'display:block;font-size:.78rem;font-weight:600;margin-bottom:5px;color:var(--p-text-muted);';

    $rows = collect($routes);
    $prices = $rows->pluck('trip_price')->filter(fn ($p) => $p > 0);
    $money = fn ($value) => number_format((float) $value, 2) . ' ' . $currency;
@endphp

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <x-panel.page-toolbar
        :title="textByLanguage('أسعار الخطوط الثابتة (سفر)', 'Fixed corridor pricing (Travel)')"
        :subtitle="textByLanguage('سعر ثابت من مدينة إلى مدينة لكل خدمة سفر — هو نفسه الذي يُعرض على الراكب', 'Flat city-to-city price per Travel service — exactly what the rider is offered')" />

    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        <x-panel.stat :label="textByLanguage('خطوط منشورة', 'Published corridors')" :value="$rows->count()" icon="bi-signpost-split" />
        <x-panel.stat :label="textByLanguage('خدمات السفر', 'Travel services')" :value="$rows->pluck('sub_service')->unique()->count()" icon="bi-bus-front" />
        <x-panel.stat :label="textByLanguage('مدن مرتبطة', 'Cities linked')"
                      :value="$rows->pluck('departure')->merge($rows->pluck('arrival'))->unique()->count()" icon="bi-geo-alt" />
        <x-panel.stat :label="textByLanguage('نطاق السعر', 'Price range')"
                      :value="$prices->isEmpty() ? '—' : $money($prices->min()) . ' – ' . $money($prices->max())" icon="bi-cash-coin" />
    </div>

    @if($subServices->isEmpty() || $cities->isEmpty())
        {{-- Without these two the form cannot be filled at all, so say what is
             missing instead of showing empty dropdowns. --}}
        <div class="p-flash p-flash--warn">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <strong>{{ textByLanguage('لا يمكن إضافة خط بعد', 'A corridor cannot be added yet') }}</strong>
                <div style="font-size:.83rem;margin-top:3px;">
                    @if($subServices->isEmpty())
                        {{ textByLanguage('لا توجد خدمة فرعية تابعة لخدمة سفر مفعّلة — أنشئ خدمة سفر وخدماتها الفرعية أولاً.', 'No sub-service belongs to an active Travel service — create a Travel service and its sub-services first.') }}
                    @endif
                    @if($cities->isEmpty())
                        {{ textByLanguage('ولا توجد مدن معرّفة لهذه الدولة — أضف المدن من مركز الإعدادات.', 'And no cities are defined for this country — add them from the settings hub.') }}
                    @endif
                </div>
                <div style="margin-top:9px;display:flex;gap:8px;flex-wrap:wrap;">
                    @if($subServices->isEmpty() && \Illuminate\Support\Facades\Route::has('panel.admin.service.index'))
                        <a href="{{ route('panel.admin.service.index') }}" class="p-btn p-btn--soft"><i class="bi bi-grid-1x2"></i> {{ textByLanguage('الخدمات', 'Services') }}</a>
                    @endif
                    @if($cities->isEmpty() && \Illuminate\Support\Facades\Route::has('panel.admin.cities.index'))
                        <a href="{{ route('panel.admin.cities.index') }}" class="p-btn p-btn--soft"><i class="bi bi-geo-alt"></i> {{ textByLanguage('المدن', 'Cities') }}</a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="p-card" style="margin-bottom:18px;">
        <div class="p-card__head">
            <h3 class="p-card__title" style="margin:0;">
                <i class="bi bi-plus-circle"></i>
                <span id="corridorFormTitle">{{ textByLanguage('إضافة خط جديد', 'Add a corridor') }}</span>
            </h3>
            <button type="button" id="corridorReset" class="p-btn p-btn--soft" style="display:none;">
                <i class="bi bi-x-lg"></i> {{ textByLanguage('إلغاء التعديل', 'Cancel edit') }}
            </button>
        </div>

        <form method="POST" action="{{ route($r('pricing.corridors.save')) }}" id="corridorForm"
              style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;align-items:end;">
            @csrf

            @if($isAdmin)
                <div>
                    <label style="{{ $lbl }}" for="office_id">{{ textByLanguage('المكتب', 'Office') }}</label>
                    <select name="office_id" id="office_id" style="{{ $inp }}" required>
                        <option value="">{{ textByLanguage('اختر مكتباً', 'Choose office') }}</option>
                        @foreach($offices as $o)
                            <option value="{{ $o->id }}">{{ $o->displayName ?? $o->officeName }} (#{{ $o->id }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label style="{{ $lbl }}" for="sub_service_id">{{ textByLanguage('خدمة السفر', 'Travel service') }}</label>
                <select name="sub_service_id" id="sub_service_id" style="{{ $inp }}" required>
                    @forelse($subServices as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @empty
                        <option value="" disabled>{{ textByLanguage('لا توجد خدمات سفر', 'No Travel services') }}</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label style="{{ $lbl }}" for="departure_city_id"><i class="bi bi-circle"></i> {{ textByLanguage('من مدينة', 'From city') }}</label>
                <select name="departure_city_id" id="departure_city_id" style="{{ $inp }}" required>
                    @foreach($cities as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="{{ $lbl }}" for="arrival_city_id"><i class="bi bi-geo-alt-fill"></i> {{ textByLanguage('إلى مدينة', 'To city') }}</label>
                <select name="arrival_city_id" id="arrival_city_id" style="{{ $inp }}" required>
                    @foreach($cities as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="{{ $lbl }}" for="trip_price">{{ textByLanguage('السعر', 'Price') }} <span dir="ltr">({{ $currency }})</span></label>
                <input type="number" step="0.01" min="0" name="trip_price" id="trip_price" style="{{ $inp }}" placeholder="0.00" required>
            </div>

            <button type="submit" class="p-btn p-btn--primary" style="height:41px;">
                <i class="bi bi-check-lg"></i> <span id="corridorSubmitLabel">{{ textByLanguage('حفظ الخط', 'Save corridor') }}</span>
            </button>
        </form>

        <p style="margin:12px 0 0;color:var(--p-text-muted);font-size:.8rem;">
            <i class="bi bi-info-circle"></i>
            {{ textByLanguage('حفظ نفس (الخدمة + المدينتين) يحدّث السعر بدل تكراره — واضغط تعديل على أي خط لتعبئته هنا.', 'Saving the same service + city pair updates the price instead of duplicating it — hit edit on any corridor to load it here.') }}
        </p>
    </div>

    @php $orphans = $rows->filter(fn ($row) => empty($row['office_id']))->count(); @endphp
    @if($orphans > 0)
        <div class="p-flash p-flash--warn">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <strong>{{ $orphans }} {{ textByLanguage('خط بلا مكتب — لا يصل للراكب', 'corridors have no office — riders never see them') }}</strong>
                <div style="font-size:.83rem;margin-top:3px;">
                    {{ textByLanguage(
                        'العرض يُبنى من خط ينشره مكتب معيّن، فالخط بلا مكتب لا يُطابق أي طلب. اضغط تعديل واختر المكتب ثم احفظ لإنشاء الخط الصحيح، واحذف القديم.',
                        'An offer is built from a corridor a specific office publishes, so an office-less one matches nothing. Hit edit, pick the office and save to create the real corridor, then delete the old row.'
                    ) }}
                </div>
            </div>
        </div>
    @endif

    <div class="p-card">
        <div class="p-card__head">
            <h3 class="p-card__title" style="margin:0;">
                <i class="bi bi-signpost-2"></i> {{ textByLanguage('الخطوط المنشورة', 'Published corridors') }}
                <span class="svc-count">({{ $rows->count() }})</span>
            </h3>
            @if($rows->count())
                <div class="p-search" style="margin:0;max-width:260px;">
                    <i class="bi bi-search"></i>
                    <input type="search" id="corridorSearch" autocomplete="off"
                           placeholder="{{ textByLanguage('ابحث بمدينة أو خدمة…', 'Search a city or service…') }}"
                           style="border:none;background:none;outline:none;font-family:inherit;font-size:.85rem;color:var(--p-text);width:100%;">
                </div>
            @endif
        </div>

        @if($rows->count())
            <x-panel.table :headers="array_filter([
                $isAdmin ? textByLanguage('المكتب', 'Office') : null,
                textByLanguage('الخدمة', 'Service'),
                textByLanguage('الخط', 'Corridor'),
                textByLanguage('السعر', 'Price'),
                '',
            ], fn ($h) => $h !== null)">
                @foreach($routes as $row)
                    <tr data-corridor
                        data-search="{{ mb_strtolower(($row['office'] ?? '') . ' ' . $row['sub_service'] . ' ' . $row['departure'] . ' ' . $row['arrival']) }}"
                        data-office="{{ $row['office_id'] }}"
                        data-service="{{ $row['sub_service_id'] }}"
                        data-from="{{ $row['departure_city_id'] }}"
                        data-to="{{ $row['arrival_city_id'] }}"
                        data-price="{{ number_format((float) $row['trip_price'], 2, '.', '') }}">
                        @if($isAdmin)
                            <td>
                                @if(empty($row['office_id']))
                                    {{-- FixedTripService matches a corridor to the office that
                                         published it, so one with no office is never offered. --}}
                                    <x-panel.badge tone="danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        {{ textByLanguage('بلا مكتب — لا يُعرض', 'No office — never offered') }}
                                    </x-panel.badge>
                                @else
                                    {{ $row['office'] ?? '#' . $row['office_id'] }}
                                @endif
                            </td>
                        @endif
                        <td><span class="p-badge p-badge--primary">{{ $row['sub_service'] }}</span></td>
                        <td>
                            <div class="p-cell-main"><div>
                                <strong>{{ $row['departure'] }} <span style="color:var(--p-text-muted);">{{ $arrow }}</span> {{ $row['arrival'] }}</strong>
                                @if((int) $row['departure_city_id'] === (int) $row['arrival_city_id'])
                                    {{-- Same city both ends: no real trip can ever match it. New ones are
                                         blocked by validation, so this only surfaces legacy rows. --}}
                                    <span class="p-cell-sub" style="color:var(--p-danger);">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        {{ textByLanguage('نفس المدينة على الطرفين — لن يُطابق أي رحلة', 'Same city both ends — no trip can match it') }}
                                    </span>
                                @endif
                            </div></div>
                        </td>
                        <td><strong style="font-size:.95rem;">{{ number_format((float) $row['trip_price'], 2) }}</strong> <span style="color:var(--p-text-muted);font-size:.8rem;">{{ $currency }}</span></td>
                        <td>
                            <div class="p-row-actions">
                                <button type="button" class="p-icon-btn" data-corridor-edit title="{{ textByLanguage('تعديل السعر', 'Edit price') }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="{{ route($r('pricing.corridors.delete'), $row['id']) }}"
                                      onsubmit="return confirm('{{ textByLanguage('حذف هذا الخط؟', 'Delete this corridor?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-icon-btn" title="{{ textByLanguage('حذف', 'Delete') }}" style="color:var(--p-danger);">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.table>
            <p id="corridorNoMatch" class="p-empty" style="display:none;">
                <i class="bi bi-search"></i> {{ textByLanguage('لا نتائج مطابقة', 'Nothing matches that search') }}
            </p>
        @else
            <p class="p-empty">
                <i class="bi bi-signpost-2"></i>
                {{ textByLanguage('لا توجد خطوط منشورة بعد — أضف أول خط من الأعلى', 'No corridors published yet — add the first one above') }}
            </p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('corridorForm');
    if (!form) return;

    var reset = document.getElementById('corridorReset');
    var title = document.getElementById('corridorFormTitle');
    var submitLabel = document.getElementById('corridorSubmitLabel');
    var addTitle = @json(textByLanguage('إضافة خط جديد', 'Add a corridor'));
    var editTitle = @json(textByLanguage('تعديل سعر خط', 'Edit corridor price'));
    var addLabel = @json(textByLanguage('حفظ الخط', 'Save corridor'));
    var editLabel = @json(textByLanguage('تحديث السعر', 'Update price'));

    function set(id, value) {
        var el = document.getElementById(id);
        if (el && value !== null && value !== undefined && value !== '') el.value = value;
    }

    function clearEdit() {
        form.reset();
        title.textContent = addTitle;
        submitLabel.textContent = addLabel;
        reset.style.display = 'none';
        document.querySelectorAll('[data-corridor]').forEach(function (r) { r.classList.remove('is-editing'); });
    }

    document.querySelectorAll('[data-corridor-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('[data-corridor]');
            // The form updates in place when the same service + cities are sent,
            // so loading a row IS editing it.
            set('office_id', row.dataset.office);
            set('sub_service_id', row.dataset.service);
            set('departure_city_id', row.dataset.from);
            set('arrival_city_id', row.dataset.to);
            set('trip_price', row.dataset.price);

            title.textContent = editTitle;
            submitLabel.textContent = editLabel;
            reset.style.display = '';

            document.querySelectorAll('[data-corridor]').forEach(function (r) { r.classList.remove('is-editing'); });
            row.classList.add('is-editing');

            form.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            document.getElementById('trip_price').focus();
        });
    });

    reset.addEventListener('click', clearEdit);

    var search = document.getElementById('corridorSearch');
    var noMatch = document.getElementById('corridorNoMatch');

    if (search) {
        search.addEventListener('input', function () {
            var q = search.value.trim().toLowerCase();
            var shown = 0;

            document.querySelectorAll('[data-corridor]').forEach(function (row) {
                var hit = q === '' || (row.dataset.search || '').indexOf(q) !== -1;
                row.style.display = hit ? '' : 'none';
                if (hit) shown++;
            });

            if (noMatch) noMatch.style.display = shown === 0 ? '' : 'none';
        });
    }
})();
</script>
@endpush

@push('styles')
<style>
    tr.is-editing td { background: rgba(248, 166, 9, .12); }
</style>
@endpush
