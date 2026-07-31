@extends('panel.layouts.master')

@use('App\Http\Services\Panel\Drivers\Logic\DocumentStatus')

@php
    $isAr = app()->getLocale() === 'ar';
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $name = trim($driver->firstName.' '.$driver->lastName) ?: '—';
    $phone = trim(($driver->dialCode ? '+'.ltrim($driver->dialCode,'+').' ' : '').$driver->phoneNumber);
    $avg = $rating['average'];
    $periodsJs = collect($overview['periods'])->map(fn ($p) => ['trips' => $p['trips'], 'revenueFormatted' => getPriceFormat($p['revenue'])])->all();
    $stars = function ($val) {
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $out .= $val >= $i ? '<i class="bi bi-star-fill"></i>' : ($val >= $i - 0.5 ? '<i class="bi bi-star-half"></i>' : '<i class="bi bi-star"></i>');
        }
        return $out;
    };
@endphp

@section('title', $name)
@section('page-title', $name)

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('بطاقة السائق', 'Driver card')" :subtitle="$name">
        <x-slot:actions>
            <a href="{{ route($r('driver.edit'), $driver->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</a>
            @if($driver->isActive)
                <form method="POST" action="{{ route($r('driver.suspend'), $driver->id) }}" style="display:inline;"
                      onsubmit="var re=prompt('{{ textByLanguage('سبب الإيقاف؟', 'Suspension reason?') }}'); if(re===null||re.trim()===''){return false;} this.reason.value=re;">
                    @csrf
                    <input type="hidden" name="reason" value="">
                    <button class="p-btn p-btn--ghost" type="submit" style="color:var(--p-danger);"><i class="bi bi-slash-circle"></i> {{ textByLanguage('إيقاف', 'Suspend') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route($r('driver.suspend'), $driver->id) }}" style="display:inline;">
                    @csrf
                    <button class="p-btn p-btn--ghost" type="submit" style="color:var(--p-success);"><i class="bi bi-check-circle"></i> {{ textByLanguage('إعادة تفعيل', 'Reinstate') }}</button>
                </form>
            @endif
            <a href="{{ route($r('driver.index')) }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $isAr ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="svc-hero">
        <div class="svc-hero__media">
            @if($driver->photo)<img src="{{ asset('storage/'.$driver->photo) }}" alt="">@else<span style="font-size:2rem;font-weight:800;">{{ mb_substr($driver->firstName ?: '؟', 0, 1) }}</span>@endif
        </div>
        <div class="svc-hero__body">
            <div class="svc-hero__tags">
                <x-panel.badge :tone="$driver->isActive ? 'success' : 'danger'">{{ $driver->isActive ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}</x-panel.badge>
                @if($driver->car_owner)<span class="p-badge p-badge--primary"><i class="bi bi-car-front"></i> {{ textByLanguage('يملك مركبة', 'Car owner') }}</span>@endif
                <span class="svc-hero__id">#{{ $driver->id }}</span>
            </div>
            <h1>{{ $name }}</h1>
            <p>
                <span dir="ltr">{{ $phone }}</span>
                @if($office) · <i class="bi bi-building"></i> {{ $office->officeName }}@endif
                @if($driver->city) · <i class="bi bi-geo-alt"></i> {{ $driver->city }}@endif
            </p>
            <div class="hero-stars">{!! $stars($avg) !!} <span>{{ number_format($avg, 1) }} ({{ $rating['count'] }})</span></div>
        </div>
        <div class="svc-hero__totals">
            <div><span class="svc-hero__num" data-count="{{ $overview['totalTrips'] }}">0</span><span class="svc-hero__lbl">{{ textByLanguage('رحلة مكتملة', 'Completed trips') }}</span></div>
            <div><span class="svc-hero__num svc-hero__num--money">{{ getPriceFormat($overview['totalRevenue']) }}</span><span class="svc-hero__lbl">{{ textByLanguage('إجمالي الإيرادات', 'Total revenue') }}</span></div>
        </div>
    </div>

    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        <x-panel.stat variant="violet" wave :label="textByLanguage('إجمالي الطلبات', 'All orders')" icon="bi-card-checklist" :value="number_format($overview['allTrips'])" />
        <x-panel.stat variant="gold" wave :label="textByLanguage('التقييم', 'Rating')" icon="bi-star-fill" :value="number_format($avg, 1)" />
        <x-panel.stat variant="royal" wave :label="textByLanguage('رصيد المحفظة', 'Wallet balance')" icon="bi-wallet2" :value="getPriceFormat($driver->walletBalance ?? 0)" />
        <x-panel.stat variant="plum" wave :label="textByLanguage('إيراد الشهر', 'Month revenue')" icon="bi-cash-stack" :value="getPriceFormat($overview['periods']['month']['revenue'])" />
    </div>

    <div class="p-card svc-stats" style="margin-bottom:18px;">
        <div class="svc-stats__head">
            <h3 class="p-card__title" style="margin:0;"><i class="bi bi-graph-up-arrow"></i> {{ textByLanguage('أداء السائق', 'Driver performance') }}</h3>
            <div class="svc-stats__controls">
                <div class="stats-tabs" id="dvTabs">
                    <button type="button" class="stats-tab is-active" data-period="today">{{ textByLanguage('اليوم', 'Today') }}</button>
                    <button type="button" class="stats-tab" data-period="week">{{ textByLanguage('الأسبوع', 'Week') }}</button>
                    <button type="button" class="stats-tab" data-period="month">{{ textByLanguage('الشهر', 'Month') }}</button>
                </div>
                <input type="date" id="dvDate" class="p-price-input" max="{{ now()->format('Y-m-d') }}" style="width:auto;">
            </div>
        </div>
        <div class="svc-stats__cards">
            <div class="svc-metric svc-metric--trips"><i class="bi bi-flag"></i><div><span class="svc-metric__num" id="dvTrips">0</span><span class="svc-metric__lbl">{{ textByLanguage('رحلة مكتملة', 'Completed trips') }}</span></div></div>
            <div class="svc-metric svc-metric--rev"><i class="bi bi-coin"></i><div><span class="svc-metric__num" id="dvRev">—</span><span class="svc-metric__lbl">{{ textByLanguage('الإيرادات', 'Revenue') }}</span></div></div>
            <div class="svc-metric__context" id="dvContext"></div>
        </div>
    </div>

    <div class="p-grid p-grid--2" style="align-items:start;">
        <div class="p-card">
            <h3 class="p-card__title"><i class="bi bi-car-front"></i> {{ textByLanguage('المركبة', 'Vehicle') }}</h3>
            @if($vehicle)
                @if($vehicle->photo)<div class="veh-photo"><img src="{{ asset('storage/'.$vehicle->photo) }}" alt=""></div>@endif
                <div class="veh-spec">
                    <div><span>{{ textByLanguage('الماركة', 'Brand') }}</span><strong>{{ $vehicle->vehicleBrand ?: '—' }}</strong></div>
                    <div><span>{{ textByLanguage('الطراز', 'Model') }}</span><strong>{{ $vehicle->model ?: '—' }}</strong></div>
                    <div><span>{{ textByLanguage('اللوحة', 'Plate') }}</span><strong dir="ltr">{{ $vehicle->plate ?: '—' }}</strong></div>
                    <div><span>{{ textByLanguage('سنة الصنع', 'Year') }}</span><strong>{{ $vehicle->modelYear ?: '—' }}</strong></div>
                    <div><span>{{ textByLanguage('اللون', 'Color') }}</span><strong>{{ $vehicle->color ?: '—' }}</strong></div>
                    <div><span>{{ textByLanguage('المقاعد', 'Seats') }}</span><strong>{{ $vehicle->seatsCount ?: '—' }}</strong></div>
                </div>
            @else
                <p class="p-empty"><i class="bi bi-car-front"></i> {{ textByLanguage('لا توجد مركبة مرتبطة', 'No linked vehicle') }}</p>
            @endif
        </div>

        <div class="p-card">
            <h3 class="p-card__title"><i class="bi bi-star"></i> {{ textByLanguage('ملخّص التقييمات', 'Ratings summary') }}</h3>
            <div class="rate-summary">
                <div class="rate-summary__avg">
                    <span class="rate-summary__num">{{ number_format($avg, 1) }}</span>
                    <div class="hero-stars">{!! $stars($avg) !!}</div>
                    <span class="rate-summary__cnt">{{ $rating['count'] }} {{ textByLanguage('تقييم', 'ratings') }}</span>
                </div>
                <div class="rate-dist">
                    @foreach($rating['distribution'] as $star => $count)
                        @php $pct = $rating['count'] ? round($count / $rating['count'] * 100) : 0; @endphp
                        <div class="rate-dist__row">
                            <span class="rate-dist__lbl">{{ $star }} <i class="bi bi-star-fill"></i></span>
                            <span class="rate-dist__bar"><span style="width: {{ $pct }}%"></span></span>
                            <span class="rate-dist__cnt">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if(isset($finance))
        @php $fmt = fn ($minor) => number_format(((int) $minor) / 100, 2) . ' ' . $finance['currency']; @endphp
        <div class="p-card" style="margin-top:18px;">
            <div class="p-card__head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-wallet2"></i> {{ textByLanguage('المحفظة والعمولة', 'Wallet & commission') }}</h3>
            </div>

            @if(session('error'))<div class="p-flash p-flash--err"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div>@endif

            <div class="p-grid p-grid--3" style="margin-bottom:16px;">
                <x-panel.stat :label="textByLanguage('رصيد المحفظة', 'Wallet balance')" :value="$fmt($finance['walletMinor'])" icon="bi-wallet2" />
                <x-panel.stat :label="textByLanguage('مستحقات على السائق', 'Owed to the fleet')" :value="$fmt($finance['duesMinor'])"
                              icon="bi-exclamation-diamond" :variant="$finance['duesMinor'] > 0 ? 'danger' : null" />
                <x-panel.stat :label="textByLanguage('العمولة المطبَّقة', 'Commission applied')"
                              :value="number_format($finance['effectiveRate'], 2) . '%'" icon="bi-percent"
                              :variant="$finance['override'] !== null ? 'primary' : null" />
            </div>

            <p style="font-size:.83rem;color:var(--p-text-muted);margin:0 0 14px;">
                @if($finance['override'] !== null)
                    <i class="bi bi-pin-angle"></i>
                    {{ textByLanguage('عمولة خاصة بهذا السائق — عمولة المكتب هي', 'Driver-specific rate — the office rate is') }}
                    {{ number_format($finance['officeRate'], 2) }}%
                @else
                    <i class="bi bi-building"></i>
                    {{ textByLanguage('يتبع عمولة المكتب', 'Following the office rate') }}
                    ({{ number_format($finance['officeRate'], 2) }}%{{ $finance['plan'] ? ' · ' . $finance['plan'] : '' }})
                @endif
            </p>

            <div style="display:flex;gap:24px;flex-wrap:wrap;">
                @if(\Illuminate\Support\Facades\Route::has($r('driver.commission')))
                    <form method="POST" action="{{ route($r('driver.commission'), $driver->id) }}" style="display:flex;gap:8px;align-items:end;">
                        @csrf
                        <div>
                            <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('عمولة خاصة %', 'Driver rate %') }}</label>
                            <input name="commission_percent" type="number" step="0.01" min="0" max="100" style="width:110px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"
                                   value="{{ $finance['override'] !== null ? number_format($finance['override'], 2, '.', '') : '' }}"
                                   placeholder="{{ number_format($finance['officeRate'], 2) }}">
                        </div>
                        <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-check-lg"></i> {{ textByLanguage('حفظ', 'Save') }}</button>
                        @if($finance['override'] !== null)
                            <button type="submit" name="commission_percent" value="" class="p-btn p-btn--soft">{{ textByLanguage('إلغاء التخصيص', 'Clear') }}</button>
                        @endif
                    </form>
                @endif

                @if($finance['duesMinor'] > 0 && \Illuminate\Support\Facades\Route::has($r('driver.dues.settle')))
                    <form method="POST" action="{{ route($r('driver.dues.settle'), $driver->id) }}" style="display:flex;gap:8px;align-items:end;"
                          onsubmit="return confirm('{{ textByLanguage('خصم المستحقات من محفظة السائق؟', 'Settle the dues from the driver wallet?') }}');">
                        @csrf
                        <div>
                            <label style="display:block;font-size:.78rem;font-weight:600;margin-bottom:4px;">{{ textByLanguage('تسوية مبلغ (فارغ = الكل)', 'Settle amount (blank = all)') }}</label>
                            <input name="amount" type="number" step="0.01" min="0.01" style="width:130px;padding:8px 10px;border:1.5px solid var(--p-border);border-radius:var(--p-radius-sm);"
                                   placeholder="{{ number_format($finance['duesMinor'] / 100, 2) }}">
                        </div>
                        <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-cash-coin"></i> {{ textByLanguage('تسوية من المحفظة', 'Settle from wallet') }}</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <div class="p-card" style="margin-top:18px;">
        <div class="p-card__head">
            <h3 class="p-card__title" style="margin:0;"><i class="bi bi-chat-quote"></i> {{ textByLanguage('آراء العملاء', 'Customer reviews') }} <span class="svc-count" id="ratingsCount"></span></h3>
        </div>
        <div class="svc-feed" id="ratingsFeed"></div>
        <button type="button" class="p-btn p-btn--ghost svc-more" id="ratingsMore" style="display:none;width:100%;margin-top:10px;">{{ textByLanguage('تحميل المزيد', 'Load more') }}</button>
    </div>

    <div class="p-card" style="margin-top:18px;">
        <div class="p-card__head">
            <h3 class="p-card__title" style="margin:0;"><i class="bi bi-file-earmark-text"></i> {{ textByLanguage('مستندات السائق', 'Driver documents') }} <span class="svc-count">({{ $documents->count() }})</span></h3>
        </div>

        <form method="POST" enctype="multipart/form-data" action="{{ route($r('driver.documents.store'), $driver->id) }}" class="doc-upload">
            @csrf
            @if(!empty($documentTypes))
                <select name="document_id" id="docTypeSelect" class="p-search__select" data-names='@json($documentTypes)'>
                    <option value="">{{ textByLanguage('نوع المستند (اختياري)', 'Document type (optional)') }}</option>
                    @foreach($documentTypes as $id => $name)
                        <option value="{{ $id }}" @selected(old('document_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            @endif
            <input type="text" name="name" id="docNameInput" placeholder="{{ textByLanguage('اسم المستند (رخصة، هوية…)', 'Document name (license, ID…)') }}" value="{{ old('name') }}" required>
            <input type="date" name="expires_at" title="{{ textByLanguage('انتهاء الصلاحية', 'Expiry') }}" value="{{ old('expires_at') }}">
            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
            <button type="submit" class="p-btn p-btn--primary"><i class="bi bi-upload"></i> {{ textByLanguage('رفع', 'Upload') }}</button>
        </form>
        @error('file')<small class="p-field__error">{{ $message }}</small>@enderror
        @error('name')<small class="p-field__error">{{ $message }}</small>@enderror

        @if($documents->count())
            <div class="doc-track">
                @foreach($documents as $doc)
                    @php
                        $isPdf = \Illuminate\Support\Str::endsWith(strtolower($doc->file), '.pdf');
                        $expired = $doc->expires_at && $doc->expires_at->isPast();
                    @endphp
                    <div class="doc-row">
                        <div class="doc-row__main">
                            <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                            <div>
                                <strong>{{ $doc->name }}</strong>
                                <span class="p-cell-sub">
                                    @if($doc->expires_at)
                                        <span class="{{ $expired ? 'doc-exp' : '' }}"><i class="bi bi-calendar-event"></i> {{ $doc->expires_at->format('Y-m-d') }}@if($expired) · {{ textByLanguage('منتهٍ', 'Expired') }}@endif</span>
                                    @endif
                                    @if($doc->note) · {{ $doc->note }}@endif
                                </span>
                            </div>
                        </div>
                        <x-panel.badge :tone="DocumentStatus::tone($doc->status)">{{ DocumentStatus::label($doc->status) }}</x-panel.badge>
                        <div class="doc-row__actions">
                            <a href="{{ asset('storage/'.$doc->file) }}" target="_blank" rel="noopener" class="p-icon-btn" title="{{ textByLanguage('عرض', 'View') }}"><i class="bi bi-box-arrow-up-{{ $isAr ? 'left' : 'right' }}"></i></a>
                            <form method="POST" action="{{ route($r('driver.documents.status'), [$driver->id, $doc->id]) }}" class="doc-status-form">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="p-search__select">
                                    @foreach($statusOptions as $val => $label)
                                        <option value="{{ $val }}" @selected($doc->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route($r('driver.documents.destroy'), [$driver->id, $doc->id]) }}" onsubmit="return confirm('{{ textByLanguage('حذف هذا المستند؟', 'Delete this document?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-icon-btn p-icon-btn--danger" title="{{ textByLanguage('حذف', 'Delete') }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="p-empty"><i class="bi bi-inbox"></i> {{ textByLanguage('لا توجد مستندات مرفوعة', 'No documents uploaded') }}</p>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        var periods = @json($periodsJs);
        var statsUrl = @json(route($r('driver.stats'), $driver->id));
        var ratingsUrl = @json(route($r('driver.ratings'), $driver->id));
        var L = { empty: @json(textByLanguage('لا توجد تقييمات', 'No ratings yet')), order: @json(textByLanguage('طلب', 'order')) };

        function animateNum(el, target) {
            var s = 0, dur = 700, t0 = null;
            function step(ts) { if (!t0) t0 = ts; var p = Math.min((ts - t0) / dur, 1); el.textContent = Math.floor(s + (target - s) * (1 - Math.pow(1 - p, 3))).toLocaleString(); if (p < 1) requestAnimationFrame(step); }
            requestAnimationFrame(step);
        }
        document.querySelectorAll('[data-count]').forEach(function (el) { animateNum(el, parseInt(el.getAttribute('data-count'), 10) || 0); });

        var tripsEl = document.getElementById('dvTrips'), revEl = document.getElementById('dvRev'), ctxEl = document.getElementById('dvContext'), dateInput = document.getElementById('dvDate');
        function showPeriod(k) { animateNum(tripsEl, periods[k].trips); revEl.textContent = periods[k].revenueFormatted; ctxEl.textContent = ''; }
        var tabs = document.querySelectorAll('#dvTabs .stats-tab');
        tabs.forEach(function (tab) { tab.addEventListener('click', function () { tabs.forEach(function (t) { t.classList.remove('is-active'); }); tab.classList.add('is-active'); dateInput.value = ''; showPeriod(tab.getAttribute('data-period')); }); });
        dateInput.addEventListener('change', function () {
            if (!dateInput.value) return;
            tabs.forEach(function (t) { t.classList.remove('is-active'); });
            tripsEl.textContent = '…'; revEl.textContent = '…';
            fetch(statsUrl + '?date=' + encodeURIComponent(dateInput.value), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) { animateNum(tripsEl, d.trips); revEl.textContent = d.revenue; ctxEl.textContent = d.label; })
                .catch(function () { tripsEl.textContent = '0'; revEl.textContent = '—'; });
        });
        showPeriod('today');

        function escapeHtml(s) { return (s || '').replace(/[&<>"]/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]; }); }
        function starRow(v) { var o = ''; for (var i = 1; i <= 5; i++) o += '<i class="bi bi-star' + (v >= i ? '-fill' : (v >= i - 0.5 ? '-half' : '')) + '"></i>'; return o; }
        function reviewHtml(it) {
            return '<div class="rev-item"><div class="rev-item__head"><span class="rev-item__stars">' + starRow(it.rating) + '</span>' +
                '<strong>' + escapeHtml(it.rater) + '</strong>' + (it.order ? '<span class="rev-item__order">' + escapeHtml(it.order) + '</span>' : '') +
                '<span class="rev-item__when">' + escapeHtml(it.when) + '</span></div>' +
                (it.comment ? '<p class="rev-item__text">' + escapeHtml(it.comment) + '</p>' : '') + '</div>';
        }

        var st = { page: 1, loading: false, done: false };
        var feed = document.getElementById('ratingsFeed'), moreBtn = document.getElementById('ratingsMore');
        function loadRatings() {
            if (st.loading || st.done) return;
            st.loading = true;
            if (st.page === 1) feed.innerHTML = '<div class="svc-skeleton"></div><div class="svc-skeleton"></div>';
            fetch(ratingsUrl + '?page=' + st.page, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (st.page === 1) { feed.innerHTML = ''; var c = document.getElementById('ratingsCount'); if (c) c.textContent = '(' + d.total + ')'; if (!d.items.length) feed.innerHTML = '<p class="p-empty" style="padding:14px;"><i class="bi bi-inbox"></i> ' + L.empty + '</p>'; }
                    d.items.forEach(function (it) { feed.insertAdjacentHTML('beforeend', reviewHtml(it)); });
                    st.loading = false;
                    if (d.hasMore) { st.page = d.nextPage; moreBtn.style.display = 'block'; } else { st.done = true; moreBtn.style.display = 'none'; }
                })
                .catch(function () { st.loading = false; });
        }
        moreBtn.addEventListener('click', loadRatings);
        new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) loadRatings(); }); }, { rootMargin: '120px' }).observe(feed);

        var typeSel = document.getElementById('docTypeSelect'), nameInput = document.getElementById('docNameInput');
        if (typeSel && nameInput) {
            typeSel.addEventListener('change', function () {
                var opt = typeSel.options[typeSel.selectedIndex];
                if (typeSel.value && opt) nameInput.value = opt.textContent;
            });
        }
    })();
</script>
@endpush
