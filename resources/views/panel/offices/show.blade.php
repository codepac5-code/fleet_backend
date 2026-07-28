@extends('panel.layouts.master')

@php
    $isAr = app()->getLocale() === 'ar';
    $logo = $office->logo ?: $office->profileImage;
    $periodsJs = collect($overview['periods'])->map(fn ($p) => ['trips' => $p['trips'], 'revenueFormatted' => getPriceFormat($p['revenue'])])->all();
@endphp

@section('title', $office->officeName)
@section('page-title', $office->officeName)

@section('content')

    @if(session('status'))
        <div class="p-flash p-flash--ok"><i class="bi bi-check-circle"></i> {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="p-flash" style="background:#fdecec;color:#842029;border:1px solid #f5c2c2;"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</div>
    @endif

    <x-panel.page-toolbar :title="textByLanguage('تفاصيل المكتب', 'Office details')" :subtitle="$office->officeName">
        <x-slot:actions>
            <a href="{{ route('panel.admin.office.documents', $office->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-file-earmark-text"></i> {{ textByLanguage('الوثائق', 'Documents') }}</a>
            <a href="{{ route('panel.admin.office.pricing.edit', $office->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-tags"></i> {{ textByLanguage('التسعير', 'Pricing') }}</a>
            <a href="{{ route('panel.admin.office.edit', $office->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</a>
            <a href="{{ route('panel.admin.office.index') }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $isAr ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="svc-hero">
        <div class="svc-hero__media">
            @if($logo)<img src="{{ asset('storage/'.$logo) }}" alt="">@else<span style="font-size:2rem;font-weight:800;">{{ mb_substr($office->officeName, 0, 1) }}</span>@endif
        </div>
        <div class="svc-hero__body">
            <div class="svc-hero__tags">
                <x-panel.badge :tone="$office->status ? 'success' : 'danger'">{{ $office->status ? textByLanguage('مفعّل', 'Active') : textByLanguage('معطّل', 'Inactive') }}</x-panel.badge>
                <span class="svc-hero__id">#{{ $office->id }}</span>
            </div>
            <h1>{{ $office->officeName }}</h1>
            <p>
                @if($office->email)<i class="bi bi-envelope"></i> {{ $office->email }}@endif
                @if($office->contactNumber) · <span dir="ltr">{{ $office->contactNumber }}</span>@endif
                @if($office->city) · <i class="bi bi-geo-alt"></i> {{ collect([$office->city, $office->country])->filter()->implode('، ') }}@endif
            </p>
        </div>
        <div class="svc-hero__totals">
            <div><span class="svc-hero__num" data-count="{{ $overview['totalTrips'] }}">0</span><span class="svc-hero__lbl">{{ textByLanguage('رحلة مكتملة', 'Completed trips') }}</span></div>
            <div><span class="svc-hero__num svc-hero__num--money">{{ getPriceFormat($overview['totalRevenue']) }}</span><span class="svc-hero__lbl">{{ textByLanguage('إجمالي الإيرادات', 'Total revenue') }}</span></div>
        </div>
    </div>

    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        <x-panel.stat variant="violet" wave :label="textByLanguage('السائقون', 'Drivers')" icon="bi-taxi-front" :value="number_format($counts['drivers'])" />
        <x-panel.stat variant="gold" wave :label="textByLanguage('المركبات', 'Vehicles')" icon="bi-car-front" :value="number_format($counts['vehicles'])" />
        <x-panel.stat variant="royal" wave :label="textByLanguage('الخدمات', 'Services')" icon="bi-diagram-3" :value="number_format($counts['services'])" />
        <x-panel.stat variant="plum" wave :label="textByLanguage('إيراد الشهر', 'Month revenue')" icon="bi-cash-stack" :value="getPriceFormat($overview['periods']['month']['revenue'])" />
    </div>

    <div class="p-grid p-grid--2" style="align-items:stretch; margin-bottom:18px;">
        <div class="wallet-pro">
            <div class="wallet-pro__top">
                <span class="wallet-pro__label"><i class="bi bi-wallet2"></i> {{ textByLanguage('رصيد المحفظة', 'Wallet balance') }}</span>
                <span class="wallet-pro__balance">{{ getPriceFormat($wallet['balance']) }}</span>
            </div>
            <div class="wallet-pro__dues">
                <div class="wallet-pro__due wallet-pro__due--fleet">
                    <span>{{ textByLanguage('مستحقات المنصّة', 'Fleet dues') }}</span>
                    <strong>{{ getPriceFormat($wallet['fleetDues']) }}</strong>
                </div>
                <div class="wallet-pro__due wallet-pro__due--drivers">
                    <span>{{ textByLanguage('مستحقات السائقين', 'Drivers dues') }}</span>
                    <strong>{{ getPriceFormat($wallet['driversDues']) }}</strong>
                </div>
            </div>
            <div class="wallet-pro__actions">
                <button type="button" class="p-btn p-btn--primary" onclick="document.getElementById('addBalModal').classList.add('open')"><i class="bi bi-plus-circle"></i> {{ textByLanguage('إضافة رصيد', 'Add balance') }}</button>
                <button type="button" class="p-btn p-btn--ghost wallet-pro__settle" onclick="document.getElementById('settleModal').classList.add('open')"><i class="bi bi-check2-circle"></i> {{ textByLanguage('تسوية المستحقات', 'Settle dues') }}</button>
            </div>
        </div>

        <div class="p-card svc-stats">
            <div class="svc-stats__head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-graph-up-arrow"></i> {{ textByLanguage('الأداء', 'Performance') }}</h3>
                <div class="svc-stats__controls">
                    <div class="stats-tabs" id="offTabs">
                        <button type="button" class="stats-tab is-active" data-period="today">{{ textByLanguage('اليوم', 'Today') }}</button>
                        <button type="button" class="stats-tab" data-period="week">{{ textByLanguage('الأسبوع', 'Week') }}</button>
                        <button type="button" class="stats-tab" data-period="month">{{ textByLanguage('الشهر', 'Month') }}</button>
                    </div>
                    <input type="date" id="offDate" class="p-price-input" max="{{ now()->format('Y-m-d') }}" style="width:auto;">
                </div>
            </div>
            <div class="svc-stats__cards">
                <div class="svc-metric svc-metric--trips"><i class="bi bi-flag"></i><div><span class="svc-metric__num" id="offTrips">0</span><span class="svc-metric__lbl">{{ textByLanguage('رحلة مكتملة', 'Completed trips') }}</span></div></div>
                <div class="svc-metric svc-metric--rev"><i class="bi bi-coin"></i><div><span class="svc-metric__num" id="offRev">—</span><span class="svc-metric__lbl">{{ textByLanguage('الإيرادات', 'Revenue') }}</span></div></div>
                <div class="svc-metric__context" id="offContext"></div>
            </div>
        </div>
    </div>

    @php
        $whDays = ['sat' => ['السبت', 'Sat'], 'sun' => ['الأحد', 'Sun'], 'mon' => ['الاثنين', 'Mon'], 'tue' => ['الثلاثاء', 'Tue'], 'wed' => ['الأربعاء', 'Wed'], 'thu' => ['الخميس', 'Thu'], 'fri' => ['الجمعة', 'Fri']];
        $wh = $office->working_hours ?? [];
    @endphp
    <div class="p-grid p-grid--2" style="margin-bottom:18px;">
        <x-panel.card :title="textByLanguage('الحالة والموقع', 'Status & location')">
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
                <x-panel.badge :tone="$office->is_verified ? 'success' : 'gray'">
                    <i class="bi bi-patch-check"></i> {{ $office->is_verified ? textByLanguage('موثّق', 'Verified') : textByLanguage('غير موثّق', 'Unverified') }}
                </x-panel.badge>
                @if($office->is_monitored)<x-panel.badge tone="warning"><i class="bi bi-eye"></i> {{ textByLanguage('تحت المراقبة', 'Monitored') }}</x-panel.badge>@endif
            </div>
            @if($office->lat && $office->lng)
                <a href="https://www.google.com/maps?q={{ $office->lat }},{{ $office->lng }}" target="_blank" rel="noopener" class="p-maplink"><i class="bi bi-geo-alt-fill"></i> {{ number_format($office->lat, 4) }}, {{ number_format($office->lng, 4) }}</a>
            @else
                <p class="p-cell-sub">{{ textByLanguage('الموقع غير محدّد', 'Location not set') }}</p>
            @endif
        </x-panel.card>
        <x-panel.card :title="textByLanguage('ساعات العمل', 'Working hours')">
            @if(!empty($wh))
                <div class="p-hours-view">
                    @foreach($whDays as $k => $lbl)
                        @php $d = $wh[$k] ?? null; $closed = !$d || !empty($d['closed']); @endphp
                        <div class="p-hours-view__row">
                            <span>{{ textByLanguage($lbl[0], $lbl[1]) }}</span>
                            <b @class(['p-hours-view__off' => $closed])>@if($closed){{ textByLanguage('مغلق', 'Closed') }}@else{{ $d['open'] }} – {{ $d['close'] }}@endif</b>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="p-cell-sub">{{ textByLanguage('غير محدّدة', 'Not set') }}</p>
            @endif
        </x-panel.card>
    </div>

    <div class="p-card">
        <div class="svc-stats__head" style="margin-bottom:14px;">
            <h3 class="p-card__title" style="margin:0;"><i class="bi bi-collection"></i> {{ textByLanguage('الموارد والخدمات', 'Resources & services') }} <span class="svc-count" id="offFeedCount"></span></h3>
            <div class="stats-tabs" id="offFeedTabs">
                <button type="button" class="stats-tab is-active" data-feed="drivers">{{ textByLanguage('السائقون', 'Drivers') }}</button>
                <button type="button" class="stats-tab" data-feed="vehicles">{{ textByLanguage('المركبات', 'Vehicles') }}</button>
                <button type="button" class="stats-tab" data-feed="services">{{ textByLanguage('الخدمات', 'Services') }}</button>
            </div>
        </div>
        <div class="svc-feed" id="offFeed"></div>
        <button type="button" class="p-btn p-btn--ghost svc-more" id="offMore" style="display:none;width:100%;margin-top:10px;">{{ textByLanguage('تحميل المزيد', 'Load more') }}</button>
    </div>

    <div class="wallet-modal" id="addBalModal">
        <div class="wallet-modal-box">
            <h4><i class="bi bi-plus-circle"></i> {{ textByLanguage('إضافة رصيد للمحفظة', 'Add wallet balance') }}</h4>
            <form method="POST" action="{{ route('panel.admin.office.balance.add', $office->id) }}">
                @csrf @method('PUT')
                <label class="p-status-label">{{ textByLanguage('المبلغ', 'Amount') }}</label>
                <div class="p-pct"><input type="number" step="0.01" min="0.01" name="amount" required autofocus></div>
                <label class="p-status-label" style="margin-top:12px;">{{ textByLanguage('ملاحظة (اختياري)', 'Note (optional)') }}</label>
                <input type="text" name="note" class="modal-note" placeholder="{{ textByLanguage('سبب الإضافة…', 'Reason…') }}">
                <div class="wallet-modal-actions">
                    <button type="button" class="wallet-btn-ghost" onclick="document.getElementById('addBalModal').classList.remove('open')">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
                    <button type="submit" class="wallet-btn-primary">{{ textByLanguage('إضافة', 'Add') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="wallet-modal" id="settleModal">
        <div class="wallet-modal-box">
            <h4><i class="bi bi-check2-circle"></i> {{ textByLanguage('تسوية مستحقات المنصّة', 'Settle fleet dues') }}</h4>
            <p class="p-hint" style="margin-bottom:12px;">{{ textByLanguage('المستحق حالياً', 'Currently due') }}: <strong>{{ getPriceFormat($wallet['fleetDues']) }}</strong></p>
            <form method="POST" action="{{ route('panel.admin.office.wallet.settle', $office->id) }}">
                @csrf @method('PUT')
                <label class="p-status-label">{{ textByLanguage('مبلغ التسوية', 'Settlement amount') }}</label>
                <div class="p-pct"><input type="number" step="0.01" min="0.01" name="amount" value="{{ $wallet['fleetDues'] > 0 ? $wallet['fleetDues'] : '' }}" required></div>
                <label class="p-status-label" style="margin-top:12px;">{{ textByLanguage('ملاحظة (اختياري)', 'Note (optional)') }}</label>
                <input type="text" name="note" class="modal-note" placeholder="{{ textByLanguage('مرجع التسوية…', 'Settlement reference…') }}">
                <div class="wallet-modal-actions">
                    <button type="button" class="wallet-btn-ghost" onclick="document.getElementById('settleModal').classList.remove('open')">{{ textByLanguage('إلغاء', 'Cancel') }}</button>
                    <button type="submit" class="wallet-btn-primary">{{ textByLanguage('تسوية', 'Settle') }}</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        var periods = @json($periodsJs);
        var statsUrl = @json(route('panel.admin.office.stats', $office->id));
        var feedUrl = @json(route('panel.admin.office.feed', $office->id));
        var L = { empty: @json(textByLanguage('لا توجد بيانات', 'No data')) };

        function animateNum(el, target) {
            var s = 0, dur = 700, t0 = null;
            function step(ts) { if (!t0) t0 = ts; var p = Math.min((ts - t0) / dur, 1); el.textContent = Math.floor(s + (target - s) * (1 - Math.pow(1 - p, 3))).toLocaleString(); if (p < 1) requestAnimationFrame(step); }
            requestAnimationFrame(step);
        }
        document.querySelectorAll('[data-count]').forEach(function (el) { animateNum(el, parseInt(el.getAttribute('data-count'), 10) || 0); });

        var tripsEl = document.getElementById('offTrips'), revEl = document.getElementById('offRev'), ctxEl = document.getElementById('offContext'), dateInput = document.getElementById('offDate');
        function showPeriod(k) { animateNum(tripsEl, periods[k].trips); revEl.textContent = periods[k].revenueFormatted; ctxEl.textContent = ''; }
        var ptabs = document.querySelectorAll('#offTabs .stats-tab');
        ptabs.forEach(function (t) { t.addEventListener('click', function () { ptabs.forEach(function (x) { x.classList.remove('is-active'); }); t.classList.add('is-active'); dateInput.value = ''; showPeriod(t.getAttribute('data-period')); }); });
        dateInput.addEventListener('change', function () {
            if (!dateInput.value) return;
            ptabs.forEach(function (x) { x.classList.remove('is-active'); });
            tripsEl.textContent = '…'; revEl.textContent = '…';
            fetch(statsUrl + '?date=' + encodeURIComponent(dateInput.value), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); }).then(function (d) { animateNum(tripsEl, d.trips); revEl.textContent = d.revenue; ctxEl.textContent = d.label; })
                .catch(function () { tripsEl.textContent = '0'; revEl.textContent = '—'; });
        });
        showPeriod('today');

        function escapeHtml(s) { return (s || '').replace(/[&<>"]/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]; }); }
        function rowHtml(it, idx) {
            return '<div class="svc-row"><span class="svc-row__rank">' + idx + '</span>' +
                '<div class="svc-row__main"><strong>' + escapeHtml(it.name) + '</strong>' + (it.meta ? '<span class="svc-row__meta">' + escapeHtml(it.meta) + '</span>' : '') + '</div>' +
                '<div class="svc-row__stats">' + (it.stat ? '<span class="svc-row__trips">' + escapeHtml(it.stat) + '</span>' : '') + '<strong class="svc-row__rev">' + escapeHtml(it.value) + '</strong></div></div>';
        }

        var feed = document.getElementById('offFeed'), moreBtn = document.getElementById('offMore'), countEl = document.getElementById('offFeedCount');
        var st = { type: 'drivers', page: 1, loading: false, done: false };
        function loadFeed(reset) {
            if (st.loading) return;
            if (reset) { st.page = 1; st.done = false; feed.innerHTML = '<div class="svc-skeleton"></div><div class="svc-skeleton"></div>'; }
            if (st.done) return;
            st.loading = true;
            fetch(feedUrl + '?type=' + st.type + '&page=' + st.page, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (st.page === 1) { feed.innerHTML = ''; if (countEl) countEl.textContent = '(' + d.total + ')'; if (!d.items.length) feed.innerHTML = '<p class="p-empty" style="padding:14px;"><i class="bi bi-inbox"></i> ' + L.empty + '</p>'; }
                    var base = feed.querySelectorAll('.svc-row').length;
                    d.items.forEach(function (it, i) { feed.insertAdjacentHTML('beforeend', rowHtml(it, base + i + 1)); });
                    st.loading = false;
                    if (d.hasMore) { st.page = d.nextPage; moreBtn.style.display = 'block'; } else { st.done = true; moreBtn.style.display = 'none'; }
                })
                .catch(function () { st.loading = false; });
        }
        var ftabs = document.querySelectorAll('#offFeedTabs .stats-tab');
        ftabs.forEach(function (t) { t.addEventListener('click', function () { ftabs.forEach(function (x) { x.classList.remove('is-active'); }); t.classList.add('is-active'); st.type = t.getAttribute('data-feed'); loadFeed(true); }); });
        moreBtn.addEventListener('click', function () { loadFeed(false); });
        loadFeed(true);

        document.querySelectorAll('.wallet-modal').forEach(function (m) { m.addEventListener('click', function (e) { if (e.target === m) m.classList.remove('open'); }); });
    })();
</script>
@endpush
