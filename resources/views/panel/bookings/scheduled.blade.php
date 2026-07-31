@extends('panel.layouts.master')

@use('App\Http\Services\Panel\Shared\Authorization\PanelPermission', 'Perm')

@section('title', textByLanguage('الرحلات المجدولة', 'Scheduled trips'))
@section('page-title', textByLanguage('الرحلات المجدولة', 'Scheduled trips'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";
    $panelUser = auth()->guard($entity)->user();
    $canEdit = $panelUser && $panelUser->can(Perm::EDIT_ORDER_STATUS);

    $tabs = [
        'upcoming'  => ['label' => textByLanguage('قادمة', 'Upcoming'),  'icon' => 'bi-hourglass-split', 'tone' => 'indigo'],
        'active'    => ['label' => textByLanguage('جارية', 'Active'),     'icon' => 'bi-broadcast',       'tone' => 'amber'],
        'completed' => ['label' => textByLanguage('مكتملة', 'Completed'), 'icon' => 'bi-check2-circle',   'tone' => 'green'],
        'cancelled' => ['label' => textByLanguage('ملغاة', 'Cancelled'),  'icon' => 'bi-x-circle',        'tone' => 'red'],
    ];

    $periodLabels = [
        'today'    => textByLanguage('اليوم', 'Today'),
        'tomorrow' => textByLanguage('غداً', 'Tomorrow'),
        'week'     => textByLanguage('هذا الأسبوع', 'This week'),
        'morning'  => textByLanguage('صباحاً', 'Morning'),
        'noon'     => textByLanguage('ظهراً', 'Noon'),
        'night'    => textByLanguage('مساءً', 'Night'),
    ];

    $i18n = [
        'assign'       => textByLanguage('إسناد سائق', 'Assign driver'),
        'change'       => textByLanguage('تغيير السائق', 'Change driver'),
        'accept'       => textByLanguage('قبول', 'Accept'),
        'cancel'       => textByLanguage('إلغاء', 'Cancel'),
        'details'      => textByLanguage('التفاصيل', 'Details'),
        'unassigned'   => textByLanguage('غير مُسنَد', 'Unassigned'),
        'customer'     => textByLanguage('العميل', 'Customer'),
        'driver'       => textByLanguage('السائق', 'Driver'),
        'empty'        => textByLanguage('لا توجد رحلات في هذه الفترة', 'No trips in this period'),
        'loading'      => textByLanguage('جارٍ التحميل…', 'Loading…'),
        'km'           => textByLanguage('كم', 'km'),
        'noDrivers'    => textByLanguage('لا سائقون متاحون', 'No available drivers'),
        'confirmTitle' => textByLanguage('تأكيد إسناد الرحلة', 'Confirm trip assignment'),
        'confirmText'  => textByLanguage('سيتم إسناد هذه الرحلة إلى:', 'This trip will be assigned to:'),
        'assignYes'    => textByLanguage('نعم، أسنِد', 'Yes, assign'),
        'back'         => textByLanguage('رجوع', 'Back'),
        'vehicle'      => textByLanguage('المركبة', 'Vehicle'),
        'noCar'        => textByLanguage('لا توجد مركبة مسجّلة', 'No registered vehicle'),
        'plate'        => textByLanguage('اللوحة', 'Plate'),
        'color'        => textByLanguage('اللون', 'Color'),
        'year'         => textByLanguage('الموديل', 'Year'),
        'seats'        => textByLanguage('المقاعد', 'Seats'),
        'assigning'    => textByLanguage('جارٍ الإسناد…', 'Assigning…'),
    ];
@endphp

@section('content')

    <x-panel.page-toolbar :title="textByLanguage('الرحلات المجدولة', 'Scheduled trips')"
        :subtitle="textByLanguage('متابعة وإدارة الرحلات المجدولة ضمن فتراتها', 'Track and manage scheduled trips by period')" />

    <div class="trip-tabs" id="tripTabs">
        @foreach($tabs as $key => $tab)
            <button type="button" class="trip-tab trip-tab--{{ $tab['tone'] }} {{ $key === 'upcoming' ? 'is-active' : '' }}" data-tab="{{ $key }}">
                <i class="bi {{ $tab['icon'] }}"></i> {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    <div class="p-card trip-filters">
        <div class="trip-filters__row">
            <div class="trip-filter">
                <label><i class="bi bi-calendar3"></i> {{ textByLanguage('التاريخ', 'Date') }}</label>
                <input type="date" id="fDate">
            </div>
            <div class="trip-filter">
                <label><i class="bi bi-person-badge"></i> {{ textByLanguage('السائق', 'Driver') }}</label>
                <select id="fDriver">
                    <option value="">{{ textByLanguage('كل السائقين', 'All drivers') }}</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select>
            </div>
            @if($isAdmin && !empty($officeOptions))
                <div class="trip-filter">
                    <label><i class="bi bi-building"></i> {{ textByLanguage('المكتب', 'Office') }}</label>
                    <select id="fOffice">
                        <option value="">{{ textByLanguage('كل المكاتب', 'All offices') }}</option>
                        @foreach($officeOptions as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="button" class="p-btn p-btn--ghost" id="fReset"><i class="bi bi-x-circle"></i> {{ textByLanguage('إزالة الفلترة', 'Reset') }}</button>
        </div>
        <div class="trip-chips" id="tripChips"></div>
    </div>

    <div class="p-grid p-grid--3" style="margin-bottom:18px;">
        <x-panel.stat variant="violet" icon="bi-layers" :label="textByLanguage('عدد الرحلات', 'Trips')" value="0" value-id="statTrips" />
        <x-panel.stat variant="gold" icon="bi-cash-coin" :label="textByLanguage('الإيرادات', 'Revenue')" value="0" value-id="statRevenue" />
        <x-panel.stat variant="royal" icon="bi-signpost-split" :label="textByLanguage('إجمالي المسافة', 'Distance')" value="0" value-id="statDistance" />
    </div>

    <div id="tripContainer" class="trip-sections"></div>
    <div id="tripLoader" class="trip-loader is-hidden"><span class="trip-spin"></span> {{ $i18n['loading'] }}</div>

    @if($canEdit)
        <div class="p-modal" id="assignModal">
            <div class="p-modal__box">
                <div class="p-modal__head">
                    <strong><i class="bi bi-person-plus"></i> {{ textByLanguage('إسناد سائق', 'Assign driver') }} <span id="assignTripRef" class="p-modal__ref"></span></strong>
                    <button type="button" class="p-modal__x" data-close><i class="bi bi-x-lg"></i></button>
                </div>
                <div style="padding:16px;">
                    <div class="p-search" style="margin-bottom:12px;">
                        <i class="bi bi-search"></i>
                        <input type="text" id="driverSearch" placeholder="{{ textByLanguage('ابحث عن سائق', 'Search driver') }}">
                    </div>
                    <div id="driverList" class="driver-list"></div>
                </div>
            </div>
        </div>

        <div class="p-modal" id="confirmAssignModal">
            <div class="p-modal__box">
                <div class="p-modal__head">
                    <strong><i class="bi bi-patch-check"></i> {{ textByLanguage('تأكيد إسناد الرحلة', 'Confirm trip assignment') }} <span id="confirmTripRef" class="p-modal__ref"></span></strong>
                    <button type="button" class="p-modal__x" data-close><i class="bi bi-x-lg"></i></button>
                </div>
                <div style="padding:18px;">
                    <div id="confirmDriverBox" class="confirm-driver"></div>
                    <div class="p-modal__actions">
                        <button type="button" class="p-btn p-btn--ghost" id="confirmBack"><i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ textByLanguage('رجوع', 'Back') }}</button>
                        <button type="button" class="p-btn p-btn--primary" id="confirmAssignBtn"><i class="bi bi-check-lg"></i> {{ textByLanguage('نعم، أسنِد', 'Yes, assign') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-modal" id="cancelModal">
            <div class="p-modal__box">
                <div class="p-modal__head">
                    <strong><i class="bi bi-x-circle"></i> {{ textByLanguage('إلغاء الرحلة', 'Cancel trip') }} <span id="cancelTripRef" class="p-modal__ref"></span></strong>
                    <button type="button" class="p-modal__x" data-close><i class="bi bi-x-lg"></i></button>
                </div>
                <div style="padding:18px;">
                    <div class="p-field">
                        <label for="cancelReason">{{ textByLanguage('سبب الإلغاء', 'Cancellation reason') }}</label>
                        <textarea id="cancelReason" rows="3" maxlength="350" placeholder="{{ textByLanguage('اكتب سبب الإلغاء…', 'Write the reason…') }}"></textarea>
                    </div>
                    <div class="p-modal__actions">
                        <button type="button" class="p-btn p-btn--ghost" data-close>{{ textByLanguage('تراجع', 'Back') }}</button>
                        <button type="button" class="p-btn p-btn--danger" id="cancelConfirm"><i class="bi bi-x-circle"></i> {{ textByLanguage('تأكيد الإلغاء', 'Confirm cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
(function () {
    const CFG = {
        dataUrl: @json(route($r('booking.scheduled.data'))),
        csrf: @json(csrf_token()),
        canEdit: @json($canEdit),
        drivers: @json($drivers),
        periods: @json($periodLabels),
        periodOrder: ['today', 'tomorrow', 'week', 'morning', 'noon', 'night'],
        tones: { upcoming: 'indigo', active: 'amber', completed: 'green', cancelled: 'red' },
        t: @json($i18n),
        rtl: @json(app()->getLocale() === 'ar'),
    };

    const state = { tab: 'upcoming', date: '', driver: '', office: '', page: 1, lastPage: 1, trips: [], loading: false };

    const container = document.getElementById('tripContainer');
    const loader = document.getElementById('tripLoader');
    const chips = document.getElementById('tripChips');

    function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

    function params() {
        const p = new URLSearchParams();
        p.set('status', state.tab);
        p.set('page', state.page);
        if (state.date) p.set('date', state.date);
        if (state.driver) p.set('driver', state.driver);
        if (state.office) p.set('office', state.office);
        return p.toString();
    }

    async function fetchData(reset) {
        if (state.loading) return;
        state.loading = true;
        loader.classList.remove('is-hidden');
        if (reset) { state.page = 1; state.trips = []; }

        try {
            const res = await fetch(CFG.dataUrl + '?' + params(), { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            state.trips = reset ? json.data : state.trips.concat(json.data);
            state.lastPage = json.last_page || 1;
            setStats(json.stats);
            render();
            renderChips();
        } catch (e) {
            console.error('scheduled fetch error', e);
        } finally {
            state.loading = false;
            loader.classList.add('is-hidden');
        }
    }

    function setStats(s) {
        if (!s) return;
        document.getElementById('statTrips').textContent = s.trips ?? 0;
        document.getElementById('statRevenue').textContent = s.revenue ?? 0;
        document.getElementById('statDistance').textContent = (s.distance ?? 0) + ' ' + CFG.t.km;
    }

    function groupByPeriod(trips) {
        const groups = {};
        trips.forEach(t => { (groups[t.period] = groups[t.period] || []).push(t); });
        return groups;
    }

    function render() {
        if (!state.trips.length) {
            container.innerHTML = '<div class="trip-empty"><i class="bi bi-calendar-x"></i> ' + esc(CFG.t.empty) + '</div>';
            return;
        }
        const groups = groupByPeriod(state.trips);
        let html = '';
        CFG.periodOrder.forEach(key => {
            const items = groups[key];
            if (!items || !items.length) return;
            html += '<section class="trip-period trip-period--' + key + '">'
                + '<header class="trip-period__head"><span>' + esc(CFG.periods[key] || key) + '</span><span class="trip-period__count">' + items.length + '</span></header>'
                + '<div class="trip-scroll">' + items.map(card).join('') + '</div>'
                + '</section>';
        });
        container.innerHTML = html;
    }

    function card(t) {
        const tone = CFG.tones[t.group] || 'indigo';
        const hasDriver = !!t.driver;
        const arrow = CFG.rtl ? 'bi-arrow-left' : 'bi-arrow-right';
        const driverLine = hasDriver
            ? '<span class="trip-driverline__on"><i class="bi bi-person-check-fill"></i> ' + esc(t.driver.name) + '</span>'
            : '<span class="trip-driverline__off"><i class="bi bi-person-dash"></i> ' + esc(CFG.t.unassigned) + '</span>';

        let actions = '<a href="' + t.urls.show + '" class="p-btn p-btn--ghost p-btn--sm"><i class="bi bi-eye"></i> ' + esc(CFG.t.details) + '</a>';
        if (CFG.canEdit && t.urls.accept) {
            actions += '<button type="button" class="p-btn p-btn--success p-btn--sm" data-accept="' + t.id + '"><i class="bi bi-check2-circle"></i> ' + esc(CFG.t.accept) + '</button>';
        }
        if (CFG.canEdit && t.urls.assign) {
            actions += '<button type="button" class="p-btn p-btn--primary p-btn--sm" data-assign="' + t.id + '"><i class="bi bi-person-plus"></i> ' + esc(hasDriver ? CFG.t.change : CFG.t.assign) + '</button>';
        }
        if (CFG.canEdit && t.urls.cancel) {
            actions += '<button type="button" class="p-btn p-btn--danger p-btn--sm" data-cancel="' + t.id + '"><i class="bi bi-x-circle"></i> ' + esc(CFG.t.cancel) + '</button>';
        }

        const driverBlock = hasDriver
            ? '<div class="trip-party"><span class="trip-party__role"><i class="bi bi-person-badge"></i> ' + esc(CFG.t.driver) + '</span><strong>' + esc(t.driver.name) + '</strong>'
                + (t.driver.phone ? '<a href="tel:' + esc(t.driver.phone) + '" class="trip-phone"><i class="bi bi-telephone"></i> ' + esc(t.driver.phone) + '</a>' : '') + '</div>'
            : '';

        return '<article class="trip-card trip-card--' + tone + '" data-card="' + t.id + '">'
            + '<header class="trip-card__head" data-toggle><span class="trip-card__id"><i class="bi bi-hash"></i>' + t.id + '</span>'
            + '<span class="trip-card__status">' + esc(t.statusLabel) + '<i class="bi bi-chevron-down trip-card__chev"></i></span></header>'
            + '<div class="trip-card__summary">'
            + '<div class="trip-route"><i class="bi bi-geo-alt-fill"></i><span>' + esc(t.startAddress || '—') + '</span><i class="bi ' + arrow + '"></i><span>' + esc(t.endAddress || '—') + '</span></div>'
            + '<div class="trip-meta"><span class="trip-when"><i class="bi bi-clock-history"></i> ' + esc(t.scheduledDisplay || t.time || '—') + '</span><span class="trip-amount">' + esc(t.amount) + '</span></div>'
            + '<div class="trip-driverline">' + driverLine + '</div>'
            + '</div>'
            + '<div class="trip-card__details">'
            + '<div class="trip-party"><span class="trip-party__role"><i class="bi bi-person"></i> ' + esc(CFG.t.customer) + '</span><strong>' + esc(t.customer.name || '—') + '</strong>'
            + (t.customer.phone ? '<a href="tel:' + esc(t.customer.phone) + '" class="trip-phone"><i class="bi bi-telephone"></i> ' + esc(t.customer.phone) + '</a>' : '') + '</div>'
            + driverBlock
            + '<div class="trip-tags"><span class="trip-tag"><i class="bi bi-credit-card"></i> ' + esc(t.paymentType || '—') + '</span>'
            + '<span class="trip-tag"><i class="bi bi-wallet2"></i> ' + esc(t.paymentStatus || '—') + '</span>'
            + '<span class="trip-tag"><i class="bi bi-signpost-split"></i> ' + esc(t.distance) + ' ' + esc(CFG.t.km) + '</span></div>'
            + '<div class="trip-actions">' + actions + '</div>'
            + '</div></article>';
    }

    function renderChips() {
        const out = [];
        if (state.date) out.push('<span class="trip-chip"><i class="bi bi-calendar3"></i> ' + esc(state.date) + ' <button data-clear="date">&times;</button></span>');
        if (state.driver) {
            const d = CFG.drivers.find(x => String(x.id) === String(state.driver));
            out.push('<span class="trip-chip"><i class="bi bi-person"></i> ' + esc(d ? d.name : state.driver) + ' <button data-clear="driver">&times;</button></span>');
        }
        chips.innerHTML = out.join('');
    }

    function applyUpdate(trip) {
        if (!trip) return;
        const i = state.trips.findIndex(t => t.id === trip.id);
        if (trip.group !== state.tab) {
            if (i !== -1) state.trips.splice(i, 1);
        } else if (i !== -1) {
            state.trips[i] = trip;
        } else {
            state.trips.unshift(trip);
        }
        render();
    }

    let assignTripId = null, cancelTripId = null;

    function openAssign(id) {
        assignTripId = id;
        document.getElementById('assignTripRef').textContent = '#' + id;
        document.getElementById('driverSearch').value = '';
        renderDriverList('');
        document.getElementById('assignModal').classList.add('open');
    }

    function renderDriverList(q) {
        const list = document.getElementById('driverList');
        const f = (q || '').toLowerCase();
        const rows = CFG.drivers.filter(d => !f || (d.name || '').toLowerCase().includes(f) || String(d.phone || '').includes(f));
        if (!rows.length) { list.innerHTML = '<div class="driver-empty">' + esc(CFG.t.noDrivers) + '</div>'; return; }
        list.innerHTML = rows.map(d => '<button type="button" class="driver-row" data-driver="' + d.id + '">'
            + '<span class="driver-av">' + esc((d.name || '#').trim().charAt(0)) + '</span>'
            + '<span class="driver-info"><strong>' + esc(d.name) + '</strong>' + (d.phone ? '<span>' + esc(d.phone) + '</span>' : '') + '</span>'
            + '<i class="bi bi-chevron-' + (CFG.rtl ? 'left' : 'right') + '"></i></button>').join('');
    }

    let selectedDriverId = null, assigning = false;

    function openConfirmAssign(driverId) {
        const d = CFG.drivers.find(x => x.id === driverId);
        if (!d) return;
        selectedDriverId = driverId;
        document.getElementById('confirmTripRef').textContent = '#' + assignTripId;
        document.getElementById('confirmDriverBox').innerHTML = confirmDriverHtml(d);
        document.getElementById('assignModal').classList.remove('open');
        document.getElementById('confirmAssignModal').classList.add('open');
    }

    function confirmDriverHtml(d) {
        const car = d.car;
        const carHtml = car
            ? '<div class="confirm-car"><div class="confirm-car__title"><i class="bi bi-car-front-fill"></i> ' + esc(CFG.t.vehicle) + '</div>'
                + '<div class="confirm-car__name">' + (esc([car.brand, car.model].filter(Boolean).join(' ')) || '—') + '</div>'
                + '<div class="confirm-car__grid">'
                + '<span><i class="bi bi-credit-card-2-front"></i> ' + esc(CFG.t.plate) + ': <b>' + esc(car.plate || '—') + '</b></span>'
                + '<span><i class="bi bi-palette"></i> ' + esc(CFG.t.color) + ': <b>' + esc(car.color || '—') + '</b></span>'
                + '<span><i class="bi bi-calendar3"></i> ' + esc(CFG.t.year) + ': <b>' + esc(car.year || '—') + '</b></span>'
                + '<span><i class="bi bi-people"></i> ' + esc(CFG.t.seats) + ': <b>' + esc(car.seats || '—') + '</b></span>'
                + '</div></div>'
            : '<div class="confirm-car confirm-car--none"><i class="bi bi-car-front"></i> ' + esc(CFG.t.noCar) + '</div>';

        return '<p class="confirm-lead">' + esc(CFG.t.confirmText) + '</p>'
            + '<div class="confirm-head"><span class="driver-av">' + esc((d.name || '#').trim().charAt(0)) + '</span>'
            + '<div class="confirm-id"><strong>' + esc(d.name) + '</strong>'
            + (d.phone ? '<span><i class="bi bi-telephone"></i> ' + esc(d.phone) + '</span>' : '') + '</div></div>'
            + carHtml;
    }

    async function assignDriver() {
        const trip = state.trips.find(t => t.id === assignTripId);
        if (!trip || selectedDriverId == null || assigning) return;
        assigning = true;
        const btn = document.getElementById('confirmAssignBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="trip-spin"></span> ' + esc(CFG.t.assigning);
        try {
            const res = await fetch(trip.urls.assign, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CFG.csrf },
                body: JSON.stringify({ driver_id: selectedDriverId }),
            });
            const json = await res.json();
            if (json.ok) {
                document.getElementById('confirmAssignModal').classList.remove('open');
                applyUpdate(json.trip);
            }
        } catch (e) {
            console.error('assign error', e);
        } finally {
            assigning = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> ' + esc(CFG.t.assignYes);
        }
    }

    let accepting = false;

    async function acceptTrip(id) {
        const trip = state.trips.find(t => t.id === id);
        if (!trip || !trip.urls.accept || accepting) return;
        accepting = true;
        try {
            const res = await fetch(trip.urls.accept, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CFG.csrf },
            });
            const json = await res.json();
            if (json.ok) applyUpdate(json.trip);
        } catch (e) {
            console.error('accept error', e);
        } finally {
            accepting = false;
        }
    }

    function openCancel(id) {
        cancelTripId = id;
        document.getElementById('cancelTripRef').textContent = '#' + id;
        document.getElementById('cancelReason').value = '';
        document.getElementById('cancelModal').classList.add('open');
    }

    async function confirmCancel() {
        const trip = state.trips.find(t => t.id === cancelTripId);
        const reason = document.getElementById('cancelReason').value.trim();
        if (!trip || !reason) return;
        const res = await fetch(trip.urls.cancel, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CFG.csrf },
            body: JSON.stringify({ reason }),
        });
        const json = await res.json();
        document.getElementById('cancelModal').classList.remove('open');
        if (json.ok) applyUpdate(json.trip);
    }

    document.getElementById('tripTabs').addEventListener('click', e => {
        const btn = e.target.closest('.trip-tab');
        if (!btn) return;
        document.querySelectorAll('.trip-tab').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        state.tab = btn.dataset.tab;
        fetchData(true);
    });

    document.getElementById('fDate').addEventListener('change', e => { state.date = e.target.value; fetchData(true); });
    document.getElementById('fDriver').addEventListener('change', e => { state.driver = e.target.value; fetchData(true); });
    const fOffice = document.getElementById('fOffice');
    if (fOffice) fOffice.addEventListener('change', e => { state.office = e.target.value; fetchData(true); });
    document.getElementById('fReset').addEventListener('click', () => {
        state.date = ''; state.driver = ''; state.office = '';
        document.getElementById('fDate').value = '';
        document.getElementById('fDriver').value = '';
        if (fOffice) fOffice.value = '';
        fetchData(true);
    });

    chips.addEventListener('click', e => {
        const btn = e.target.closest('[data-clear]');
        if (!btn) return;
        const k = btn.dataset.clear;
        state[k] = '';
        if (k === 'date') document.getElementById('fDate').value = '';
        if (k === 'driver') document.getElementById('fDriver').value = '';
        fetchData(true);
    });

    container.addEventListener('click', e => {
        const head = e.target.closest('[data-toggle]');
        if (head) { head.closest('.trip-card').classList.toggle('is-open'); return; }
        const ac = e.target.closest('[data-accept]');
        if (ac) { acceptTrip(parseInt(ac.dataset.accept, 10)); return; }
        const a = e.target.closest('[data-assign]');
        if (a) { openAssign(parseInt(a.dataset.assign, 10)); return; }
        const c = e.target.closest('[data-cancel]');
        if (c) { openCancel(parseInt(c.dataset.cancel, 10)); return; }
    });

    if (CFG.canEdit) {
        document.getElementById('driverSearch').addEventListener('input', e => renderDriverList(e.target.value));
        document.getElementById('driverList').addEventListener('click', e => {
            const row = e.target.closest('[data-driver]');
            if (row) openConfirmAssign(parseInt(row.dataset.driver, 10));
        });
        document.getElementById('confirmAssignBtn').addEventListener('click', assignDriver);
        document.getElementById('confirmBack').addEventListener('click', () => {
            document.getElementById('confirmAssignModal').classList.remove('open');
            document.getElementById('assignModal').classList.add('open');
        });
        document.getElementById('cancelConfirm').addEventListener('click', confirmCancel);
        document.querySelectorAll('.p-modal [data-close]').forEach(b => b.addEventListener('click', () => b.closest('.p-modal').classList.remove('open')));
        document.querySelectorAll('.p-modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));
    }

    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 120) {
            if (!state.loading && state.page < state.lastPage) { state.page++; fetchData(false); }
        }
    });

    fetchData(true);
})();
</script>
@endpush
