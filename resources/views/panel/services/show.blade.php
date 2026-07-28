@extends('panel.layouts.master')

@php
    $isAr = app()->getLocale() === 'ar';
    $title = $isAr ? ($service->title ?: $service->title_en) : ($service->title_en ?: $service->title);
    $desc = $isAr ? ($service->description ?: $service->description_en) : ($service->description_en ?: $service->description);
    $periodsJs = collect($overview['periods'])->map(fn ($p) => [
        'trips'            => $p['trips'],
        'revenueFormatted' => getPriceFormat($p['revenue']),
    ])->all();
@endphp

@section('title', $title)
@section('page-title', $title)

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('تفاصيل الخدمة', 'Service details')" :subtitle="$title">
        <x-slot:actions>
            <a href="{{ route('panel.admin.service.sub.index', $service->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-diagram-3"></i> {{ textByLanguage('الخدمات الفرعية', 'Sub-services') }}</a>
            <a href="{{ route('panel.admin.service.edit', $service->id) }}" class="p-btn p-btn--ghost"><i class="bi bi-pencil"></i> {{ textByLanguage('تعديل', 'Edit') }}</a>
            <a href="{{ route('panel.admin.service.index') }}" class="p-btn p-btn--ghost"><i class="bi bi-arrow-{{ $isAr ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}</a>
        </x-slot:actions>
    </x-panel.page-toolbar>

    <div class="svc-hero">
        <div class="svc-hero__media">
            @if($service->image)
                <img src="{{ asset('storage/'.$service->image) }}" alt="">
            @else
                <i class="bi bi-grid-1x2"></i>
            @endif
        </div>
        <div class="svc-hero__body">
            <div class="svc-hero__tags">
                <x-panel.badge :tone="$service->status ? 'success' : 'danger'">{{ $service->status ? textByLanguage('مفعّلة', 'Active') : textByLanguage('معطّلة', 'Inactive') }}</x-panel.badge>
                @if($service->travel_service)<span class="p-badge p-badge--primary"><i class="bi bi-airplane"></i> {{ textByLanguage('خدمة سفر', 'Travel') }}</span>@endif
                <span class="svc-hero__id">#{{ $service->id }}</span>
            </div>
            <h1>{{ $title }}</h1>
            @if($desc)<p>{{ $desc }}</p>@endif
        </div>
        <div class="svc-hero__totals">
            <div><span class="svc-hero__num" data-count="{{ $overview['totalTrips'] }}">0</span><span class="svc-hero__lbl">{{ textByLanguage('إجمالي الرحلات', 'Total trips') }}</span></div>
            <div><span class="svc-hero__num svc-hero__num--money">{{ getPriceFormat($overview['totalRevenue']) }}</span><span class="svc-hero__lbl">{{ textByLanguage('إجمالي الإيرادات', 'Total revenue') }}</span></div>
        </div>
    </div>

    <div class="p-grid p-grid--4" style="margin-bottom:18px;">
        <x-panel.stat variant="violet" wave :label="textByLanguage('الخدمات الفرعية', 'Sub-services')" icon="bi-diagram-3" :value="number_format($subServices->count())" />
        <x-panel.stat variant="gold" wave :label="textByLanguage('المكاتب المستخدِمة', 'Offices')" icon="bi-building" :value="number_format($overview['offices'])" />
        <x-panel.stat variant="royal" wave :label="textByLanguage('السائقون', 'Drivers')" icon="bi-taxi-front" :value="number_format($overview['drivers'])" />
        <x-panel.stat variant="plum" wave :label="textByLanguage('إيراد الشهر', 'This month revenue')" icon="bi-cash-stack" :value="getPriceFormat($overview['periods']['month']['revenue'])" />
    </div>

    <div class="p-card svc-stats" style="margin-bottom:18px;">
        <div class="svc-stats__head">
            <h3 class="p-card__title" style="margin:0;"><i class="bi bi-graph-up-arrow"></i> {{ textByLanguage('ملخّص الأداء', 'Performance summary') }}</h3>
            <div class="svc-stats__controls">
                <div class="stats-tabs" id="svcTabs">
                    <button type="button" class="stats-tab is-active" data-period="today">{{ textByLanguage('اليوم', 'Today') }}</button>
                    <button type="button" class="stats-tab" data-period="week">{{ textByLanguage('الأسبوع', 'Week') }}</button>
                    <button type="button" class="stats-tab" data-period="month">{{ textByLanguage('الشهر', 'Month') }}</button>
                </div>
                <input type="date" id="svcDate" class="p-price-input" max="{{ now()->format('Y-m-d') }}" style="width:auto;">
            </div>
        </div>
        <div class="svc-stats__cards">
            <div class="svc-metric svc-metric--trips">
                <i class="bi bi-flag"></i>
                <div><span class="svc-metric__num" id="svcTrips">0</span><span class="svc-metric__lbl" id="svcTripsLbl">{{ textByLanguage('رحلة مكتملة', 'Completed trips') }}</span></div>
            </div>
            <div class="svc-metric svc-metric--rev">
                <i class="bi bi-coin"></i>
                <div><span class="svc-metric__num" id="svcRev">—</span><span class="svc-metric__lbl">{{ textByLanguage('الإيرادات', 'Revenue') }}</span></div>
            </div>
            <div class="svc-metric__context" id="svcContext"></div>
        </div>
    </div>

    <div class="p-card" style="margin-bottom:18px;">
        <h3 class="p-card__title"><i class="bi bi-diagram-3"></i> {{ textByLanguage('الخدمات الفرعية', 'Sub-services') }}</h3>
        @if($subServices->count())
            <div class="svc-sub-grid">
                @foreach($subServices as $sub)
                    @php $sname = $isAr ? ($sub->name ?: $sub->name_en) : ($sub->name_en ?: $sub->name); @endphp
                    <div class="svc-sub {{ $sub->status ? '' : 'is-off' }}">
                        <div class="svc-sub__top">
                            <strong>{{ $sname }}</strong>
                            <span class="svc-sub__dot {{ $sub->status ? 'on' : 'off' }}"></span>
                        </div>
                        <div class="svc-sub__prices">
                            <span><i class="bi bi-unlock"></i> {{ getPriceFormat($sub->openPrice) }}</span>
                            <span><i class="bi bi-signpost-2"></i> {{ getPriceFormat($sub->kmPrice) }}</span>
                            <span><i class="bi bi-clock"></i> {{ getPriceFormat($sub->minutePrice) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="p-empty"><i class="bi bi-inbox"></i> {{ textByLanguage('لا توجد خدمات فرعية', 'No sub-services') }}</p>
        @endif
    </div>

    <div class="p-grid p-grid--2" style="align-items:start;">
        <div class="p-card">
            <div class="p-card__head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-taxi-front"></i> {{ textByLanguage('السائقون الأكثر نشاطاً', 'Top drivers') }} <span class="svc-count" id="driversCount"></span></h3>
            </div>
            <div class="svc-feed" id="driversFeed" data-type="drivers"></div>
            <button type="button" class="p-btn p-btn--ghost svc-more" data-more="drivers" style="display:none;width:100%;margin-top:10px;">{{ textByLanguage('تحميل المزيد', 'Load more') }}</button>
        </div>

        <div class="p-card">
            <div class="p-card__head">
                <h3 class="p-card__title" style="margin:0;"><i class="bi bi-building"></i> {{ textByLanguage('المكاتب المستخدِمة', 'Offices') }} <span class="svc-count" id="officesCount"></span></h3>
            </div>
            <div class="svc-feed" id="officesFeed" data-type="offices"></div>
            <button type="button" class="p-btn p-btn--ghost svc-more" data-more="offices" style="display:none;width:100%;margin-top:10px;">{{ textByLanguage('تحميل المزيد', 'Load more') }}</button>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        var periods = @json($periodsJs);
        var statsUrl = @json(route('panel.admin.service.stats', $service->id));
        var feedUrl = @json(route('panel.admin.service.feed', $service->id));
        var labels = {
            completed: @json(textByLanguage('رحلة مكتملة', 'Completed trips')),
            noPhone: @json(textByLanguage('بدون هاتف', 'No phone')),
            empty: @json(textByLanguage('لا توجد بيانات', 'No data')),
            trips: @json(textByLanguage('رحلة', 'trips')),
            drivers: @json(textByLanguage('سائق', 'drivers'))
        };

        function animateNum(el, target) {
            var start = 0, dur = 700, t0 = null;
            function step(ts) {
                if (!t0) t0 = ts;
                var p = Math.min((ts - t0) / dur, 1);
                el.textContent = Math.floor(start + (target - start) * (1 - Math.pow(1 - p, 3))).toLocaleString();
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        document.querySelectorAll('[data-count]').forEach(function (el) {
            animateNum(el, parseInt(el.getAttribute('data-count'), 10) || 0);
        });

        var tripsEl = document.getElementById('svcTrips');
        var revEl = document.getElementById('svcRev');
        var ctxEl = document.getElementById('svcContext');
        var dateInput = document.getElementById('svcDate');

        function showPeriod(key) {
            animateNum(tripsEl, periods[key].trips);
            revEl.textContent = periods[key].revenueFormatted;
            ctxEl.textContent = '';
        }

        var tabs = document.querySelectorAll('#svcTabs .stats-tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('is-active'); });
                tab.classList.add('is-active');
                dateInput.value = '';
                showPeriod(tab.getAttribute('data-period'));
            });
        });

        dateInput.addEventListener('change', function () {
            if (!dateInput.value) return;
            tabs.forEach(function (t) { t.classList.remove('is-active'); });
            tripsEl.textContent = '…'; revEl.textContent = '…'; ctxEl.textContent = '';
            fetch(statsUrl + '?date=' + encodeURIComponent(dateInput.value), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    animateNum(tripsEl, d.trips);
                    revEl.textContent = d.revenue;
                    ctxEl.textContent = d.label;
                })
                .catch(function () { tripsEl.textContent = '0'; revEl.textContent = '—'; });
        });

        showPeriod('today');

        var feedState = {
            drivers: { page: 1, loading: false, done: false },
            offices: { page: 1, loading: false, done: false }
        };

        function rowHtml(type, it, idx) {
            var extra = type === 'drivers'
                ? '<span class="svc-row__rate"><i class="bi bi-star-fill"></i> ' + it.extra + '</span>'
                : '<span class="svc-row__rate"><i class="bi bi-taxi-front"></i> ' + it.extra + ' ' + labels.drivers + '</span>';
            return '<div class="svc-row">' +
                '<span class="svc-row__rank">' + idx + '</span>' +
                '<div class="svc-row__main"><strong>' + escapeHtml(it.name) + '</strong>' +
                '<span class="svc-row__meta">' + (escapeHtml(it.meta) || labels.noPhone) + '</span></div>' +
                '<div class="svc-row__stats">' + extra +
                '<span class="svc-row__trips">' + it.trips + ' ' + labels.trips + '</span>' +
                '<strong class="svc-row__rev">' + it.revenue + '</strong></div></div>';
        }
        function escapeHtml(s) { return (s || '').replace(/[&<>"]/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]; }); }

        function loadFeed(type) {
            var st = feedState[type];
            if (st.loading || st.done) return;
            st.loading = true;
            var container = document.getElementById(type + 'Feed');
            var moreBtn = document.querySelector('[data-more="' + type + '"]');
            if (st.page === 1) container.innerHTML = '<div class="svc-skeleton"></div><div class="svc-skeleton"></div><div class="svc-skeleton"></div>';
            fetch(feedUrl + '?type=' + type + '&page=' + st.page, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (st.page === 1) {
                        container.innerHTML = '';
                        var countEl = document.getElementById(type + 'Count');
                        if (countEl) countEl.textContent = '(' + d.total + ')';
                        if (!d.items.length) container.innerHTML = '<p class="p-empty" style="padding:14px;"><i class="bi bi-inbox"></i> ' + labels.empty + '</p>';
                    }
                    var base = container.querySelectorAll('.svc-row').length;
                    d.items.forEach(function (it, i) { container.insertAdjacentHTML('beforeend', rowHtml(type, it, base + i + 1)); });
                    st.loading = false;
                    if (d.hasMore) { st.page = d.nextPage; moreBtn.style.display = 'block'; }
                    else { st.done = true; moreBtn.style.display = 'none'; }
                })
                .catch(function () { st.loading = false; });
        }

        document.querySelectorAll('.svc-more').forEach(function (btn) {
            btn.addEventListener('click', function () { loadFeed(btn.getAttribute('data-more')); });
        });

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) loadFeed(e.target.getAttribute('data-type'));
            });
        }, { rootMargin: '120px' });
        io.observe(document.getElementById('driversFeed'));
        io.observe(document.getElementById('officesFeed'));
    })();
</script>
@endpush
