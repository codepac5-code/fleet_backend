@extends('panel.layouts.master')

@section('title', textByLanguage('الرحلات الفورية', 'Live trips'))

@php
    $r = fn ($name) => "panel.{$entity}.{$name}";

    $i18n = [
        'heading'    => textByLanguage('الرصد الحيّ للرحلات', 'Live trip monitor'),
        'live'       => textByLanguage('بثّ مباشر', 'Live'),
        'polling'    => textByLanguage('تحديث دوري', 'Polling'),
        'offline'    => textByLanguage('غير متصل', 'Offline'),
        'connecting' => textByLanguage('جارٍ الاتصال…', 'Connecting…'),
        'active'     => textByLanguage('رحلة نشطة', 'active'),
        'fleet'      => textByLanguage('أسطول النظام', 'System fleet'),
        'noDriver'   => textByLanguage('بانتظار إسناد سائق', 'Awaiting driver'),
        'details'    => textByLanguage('تفاصيل كاملة', 'Full details'),
        'call'       => textByLanguage('اتصال', 'Call'),
        'empty'      => textByLanguage('لا رحلات فوريّة الآن', 'No live trips right now'),
        'waiting'    => textByLanguage('بانتظار رحلات حيّة…', 'Listening for live trips…'),
        'noOffice'   => textByLanguage('لا مكاتب نشطة', 'No active offices'),
        'searchOff'  => textByLanguage('ابحث عن مكتب', 'Search office'),
        'km'         => textByLanguage('كم', 'km'),
        'shared'     => textByLanguage('مشترك للجميع', 'Shared'),
        'colSearch'  => textByLanguage('بحث وإسناد', 'Searching'),
        'colOngoing' => textByLanguage('جارية', 'Ongoing'),
        'colDone'    => textByLanguage('مكتملة', 'Completed'),
        'legPending' => textByLanguage('بحث/إسناد', 'Searching'),
        'legOngoing' => textByLanguage('جارية', 'Ongoing'),
        'legDone'    => textByLanguage('مكتملة', 'Completed'),
        'secDriver'  => textByLanguage('السائق', 'Driver'),
        'secCar'     => textByLanguage('المركبة', 'Vehicle'),
        'secCustomer' => textByLanguage('العميل', 'Customer'),
        'secTrip'    => textByLanguage('تفاصيل الرحلة', 'Trip details'),
        'payment'    => textByLanguage('طريقة الدفع', 'Payment'),
        'payStatus'  => textByLanguage('حالة الدفع', 'Pay status'),
        'plate'      => textByLanguage('اللوحة', 'Plate'),
        'color'      => textByLanguage('اللون', 'Color'),
        'year'       => textByLanguage('الموديل', 'Year'),
        'seats'      => textByLanguage('المقاعد', 'Seats'),
        'findPh'     => textByLanguage('رقم الرحلة', 'Trip #'),
        'notFound'   => textByLanguage('لا توجد رحلة فوريّة بهذا الرقم', 'No live trip with this number'),
        'action'     => textByLanguage('إجراء', 'Action'),
        'actTitle'   => textByLanguage('إجراء على الرحلة', 'Trip action'),
        'actHint'    => textByLanguage('اختر الإجراء المطلوب', 'Choose an action'),
        'actHold'    => textByLanguage('تعليق', 'Hold'),
        'actPaid'    => textByLanguage('إكمال مع دفع', 'Complete · paid'),
        'actUnpaid'  => textByLanguage('إكمال بدون دفع', 'Complete · unpaid'),
        'actCancel'  => textByLanguage('إلغاء الرحلة', 'Cancel trip'),
        'reasonPh'   => textByLanguage('سبب الإلغاء…', 'Cancellation reason…'),
        'confirmAct' => textByLanguage('تأكيد الإجراء', 'Confirm action'),
        'back'       => textByLanguage('رجوع', 'Back'),
        'chosen'     => textByLanguage('الإجراء المختار', 'Chosen action'),
        'chMath'     => textByLanguage('للتأكيد، اكتب ناتج: :op', 'To confirm, type the result of: :op'),
        'chWord'     => textByLanguage('للتأكيد، اكتب الكلمة: :word', 'To confirm, type the word: :word'),
        'chWordVal'  => textByLanguage('متأكد', 'confirm'),
        'chPh'       => textByLanguage('اكتب التأكيد هنا', 'Type confirmation here'),
        'stages'     => [
            textByLanguage('طلب', 'Request'),
            textByLanguage('الطريق', 'On way'),
            textByLanguage('انتظار', 'Waiting'),
            textByLanguage('انطلاق', 'Started'),
            textByLanguage('اكتمال', 'Done'),
        ],
    ];
@endphp

@section('content')

<div class="live-wrap">

    <div class="live-head">
        <div class="live-head__brand">
            <span class="live-radar"><span></span><span></span><span></span><i class="bi bi-broadcast"></i></span>
            <div class="live-head__meta">
                <h2>{{ $i18n['heading'] }}</h2>
                <p><b id="liveActiveCount">0</b> {{ $i18n['active'] }}</p>
            </div>
        </div>
        <div class="live-head__tools">
            <div class="live-find">
                <i class="bi bi-hash"></i>
                <input type="number" id="tripFind" min="1" placeholder="{{ $i18n['findPh'] }}">
                <button type="button" id="tripFindBtn"><i class="bi bi-search"></i></button>
            </div>
            <div class="live-legend">
                <span class="live-legend__i live-legend__i--indigo">{{ $i18n['legPending'] }}</span>
                <span class="live-legend__i live-legend__i--amber">{{ $i18n['legOngoing'] }}</span>
                <span class="live-legend__i live-legend__i--green">{{ $i18n['legDone'] }}</span>
            </div>
            <div class="live-status live-status--connecting" id="liveStatus">
                <span class="live-status__dot"></span>
                <span class="live-status__text">{{ $i18n['connecting'] }}</span>
            </div>
        </div>
    </div>

    <div class="live-railbar" id="liveRailBar">
        @if($isAdmin)
            <div class="live-search">
                <i class="bi bi-search"></i>
                <input type="text" id="railSearch" placeholder="{{ $i18n['searchOff'] }}">
            </div>
        @endif
        <div class="live-rail" id="liveRail"></div>
    </div>

    <div class="live-empty is-hidden" id="liveEmpty"><i class="bi bi-broadcast"></i><span>{{ $i18n['empty'] }}</span></div>

    <div class="live-cols" id="liveCols">
        <section class="live-col live-col--indigo" data-col="searching">
            <header class="live-col__head">
                <span class="live-col__title"><i class="bi bi-broadcast-pin"></i> {{ $i18n['colSearch'] }}
                    <span class="live-col__shared"><i class="bi bi-people-fill"></i> {{ $i18n['shared'] }}</span></span>
                <span class="live-col__count"><span class="live-src__ping"></span><b data-count="searching">0</b></span>
            </header>
            <div class="live-col__beam"></div>
            <div class="live-col__body" data-body="searching"></div>
        </section>

        <section class="live-col live-col--amber" data-col="ongoing">
            <header class="live-col__head">
                <span class="live-col__title"><i class="bi bi-truck"></i> {{ $i18n['colOngoing'] }}
                    <span class="live-col__src" data-coname="ongoing"></span></span>
                <span class="live-col__count"><span class="live-src__ping"></span><b data-count="ongoing">0</b></span>
            </header>
            <div class="live-col__beam"></div>
            <div class="live-col__body" data-body="ongoing"></div>
        </section>

        <section class="live-col live-col--green" data-col="completed">
            <header class="live-col__head">
                <span class="live-col__title"><i class="bi bi-check2-circle"></i> {{ $i18n['colDone'] }}
                    <span class="live-col__src" data-coname="completed"></span></span>
                <span class="live-col__count"><b data-count="completed">0</b></span>
            </header>
            <div class="live-col__beam"></div>
            <div class="live-col__body" data-body="completed"></div>
        </section>
    </div>

    @if($canEdit)
        <div class="p-modal" id="liveActionModal">
            <div class="p-modal__box">
                <div class="p-modal__head">
                    <strong><i class="bi bi-sliders"></i> {{ $i18n['actTitle'] }} <span id="actRef" class="p-modal__ref"></span></strong>
                    <button type="button" class="p-modal__x" data-close><i class="bi bi-x-lg"></i></button>
                </div>
                <div style="padding:16px;">
                    <div id="actChoices">
                        <p class="act-hint">{{ $i18n['actHint'] }}</p>
                        <div class="live-acts">
                            <button type="button" class="lt-btn lt-btn--hold" data-act="hold"><i class="bi bi-pause-circle-fill"></i> {{ $i18n['actHold'] }}</button>
                            <button type="button" class="lt-btn lt-btn--done" data-act="complete_paid"><i class="bi bi-cash-coin"></i> {{ $i18n['actPaid'] }}</button>
                            <button type="button" class="lt-btn lt-btn--done2" data-act="complete_unpaid"><i class="bi bi-check2-circle"></i> {{ $i18n['actUnpaid'] }}</button>
                            <button type="button" class="lt-btn lt-btn--cancel" data-act="cancel"><i class="bi bi-x-octagon-fill"></i> {{ $i18n['actCancel'] }}</button>
                        </div>
                    </div>

                    <div id="actConfirm" class="act-confirm is-hidden">
                        <div class="act-chosen"><span class="act-chosen__lbl">{{ $i18n['chosen'] }}</span><span id="actChosen" class="act-chosen__v"></span></div>
                        <textarea id="actReason" class="act-reason is-hidden" rows="3" maxlength="350" placeholder="{{ $i18n['reasonPh'] }}"></textarea>
                        <label class="act-ch__q" id="actChQ"></label>
                        <input type="text" id="actChIn" class="act-ch__in" placeholder="{{ $i18n['chPh'] }}" autocomplete="off">
                        <div class="act-confirm__btns">
                            <button type="button" class="lt-btn lt-btn--ghost" id="actBack"><i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ $i18n['back'] }}</button>
                            <button type="button" class="lt-btn" id="actGo"><i class="bi bi-check-lg"></i> {{ $i18n['confirmAct'] }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/socket.io.min.js') }}"></script>
<script>
(function () {
    const CFG = {
        summaryUrl: @json(route($r('booking.live.summary'))),
        companyUrl: @json(route($r('booking.live.company'))),
        findUrl: @json(route($r('booking.live.find'))),
        actionBase: @json($canEdit ? route($r('booking.live.action'), ['booking' => '__ID__']) : null),
        showBase: @json(route($r('booking.show'), ['booking' => '__ID__'])),
        showRideBase: @json($showRideBase ?? null),
        csrf: @json(csrf_token()),
        canEdit: @json($canEdit),
        realtimeUrl: @json($realtimeUrl),
        channel: @json($realtimeChannel),
        realtimeEnabled: @json($realtimeEnabled),
        isAdmin: @json($isAdmin),
        t: @json($i18n),
        rtl: @json(app()->getLocale() === 'ar'),
    };

    const TONE  = { pending: 'indigo', ongoing: 'amber', completed: 'green' };
    const SICON = { pending: 'bi-broadcast-pin', ongoing: 'bi-truck', completed: 'bi-check2-circle' };

    const state = {
        searching: [], company: null, companyTrips: [], sources: [],
        query: '', loadingCompany: false, open: new Set(),
        findId: null, actionId: null, posting: false,
        poll: null, sumTimer: null, mode: 'connecting', dirty: false,
    };

    const bodies = {
        searching: document.querySelector('[data-body="searching"]'),
        ongoing:   document.querySelector('[data-body="ongoing"]'),
        completed: document.querySelector('[data-body="completed"]'),
    };
    const rail = document.getElementById('liveRail');
    const cols = document.getElementById('liveCols');
    const emptyBox = document.getElementById('liveEmpty');

    function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }
    function showUrl(t) {
        var base = (t && t.isRide && CFG.showRideBase) ? CFG.showRideBase : CFG.showBase;
        return base.replace('__ID__', t && t.id != null ? t.id : t);
    }
    function initial(n) { return ((n || '#').trim().charAt(0) || '#'); }
    function makeEl(html) { const d = document.createElement('div'); d.innerHTML = html.trim(); return d.firstElementChild; }
    function srcKey(t) { return t.source ? t.source.key : 'fleet'; }

    function setStatus(mode) {
        state.mode = mode;
        const box = document.getElementById('liveStatus');
        const txt = { live: CFG.t.live, polling: CFG.t.polling, offline: CFG.t.offline, connecting: CFG.t.connecting }[mode] || '';
        box.className = 'live-status live-status--' + mode;
        box.querySelector('.live-status__text').textContent = txt;
    }

    function ensureCompany() {
        const keys = state.sources.map(s => s.key);
        if (state.company && keys.includes(state.company)) return false;
        const fleet = state.sources.find(s => s.isFleet);
        const next = fleet ? fleet.key : (state.sources[0] ? state.sources[0].key : null);
        if (next !== state.company) { state.company = next; return true; }
        return false;
    }

    function companyLabel() {
        const s = state.sources.find(x => x.key === state.company);
        return s ? s.label : '';
    }

    function renderRail() {
        if (!CFG.isAdmin && state.sources.length <= 1) { document.getElementById('liveRailBar').classList.add('is-hidden'); return; }
        document.getElementById('liveRailBar').classList.remove('is-hidden');
        const q = state.query.trim().toLowerCase();
        const list = q ? state.sources.filter(s => String(s.label).toLowerCase().includes(q)) : state.sources;
        if (!list.length) { rail.innerHTML = '<div class="live-rail__none">' + esc(CFG.t.noOffice) + '</div>'; return; }
        rail.innerHTML = list.map(s => {
            const icon = s.isFleet ? 'bi-broadcast-pin' : 'bi-building';
            const active = s.key === state.company ? ' is-active' : '';
            const fleet = s.isFleet ? ' live-rail__item--fleet' : '';
            return '<button type="button" class="live-rail__item' + fleet + active + '" data-company="' + esc(s.key) + '">'
                + '<i class="bi ' + icon + '"></i><span>' + esc(s.label) + '</span>'
                + '<span class="live-rail__n">' + s.total + '</span></button>';
        }).join('');
    }

    function stepper(stage) {
        let nodes = '';
        for (let i = 1; i <= 5; i++) {
            const cls = (stage >= i ? ' is-done' : '') + (stage === i ? ' is-active' : '');
            nodes += '<span class="lt-step' + cls + '"><b></b><em>' + esc(CFG.t.stages[i - 1]) + '</em></span>';
        }
        return '<div class="lt-stepper">' + nodes + '</div>';
    }

    function section(icon, title, body, tone) {
        return '<div class="lt-section' + (tone ? ' lt-section--' + tone : '') + '">'
            + '<div class="lt-section__h"><i class="bi ' + icon + '"></i> ' + esc(title) + '</div>'
            + '<div class="lt-section__b">' + body + '</div></div>';
    }

    function spec(icon, label, value) {
        return '<span class="lt-spec"><i class="bi ' + icon + '"></i><em>' + esc(label) + '</em><b>' + esc(value || '—') + '</b></span>';
    }

    function cardHTML(t) {
        const tone = TONE[t.group] || 'indigo';
        const arrow = CFG.rtl ? 'bi-arrow-left' : 'bi-arrow-right';
        const hasDriver = !!t.driver;
        const car = hasDriver ? t.driver.car : null;

        const driverline = hasDriver
            ? '<span class="lt-who lt-who--on"><span class="lt-av lt-av--sm">' + esc(initial(t.driver.name)) + '</span>'
                + '<span class="lt-who__txt"><strong>' + esc(t.driver.name) + '</strong>'
                + (t.driver.vehicleText ? '<em>' + esc(t.driver.vehicleText) + '</em>' : '') + '</span></span>'
            : '<span class="lt-who lt-who--off"><i class="bi bi-person-dash"></i> ' + esc(CFG.t.noDriver) + '</span>';

        const driverSec = hasDriver ? section('bi-person-badge', CFG.t.secDriver,
            '<div class="lt-id"><span class="lt-av">' + esc(initial(t.driver.name)) + '</span>'
            + '<div class="lt-id__b"><strong>' + esc(t.driver.name) + '</strong>'
            + (t.driver.phone ? '<span class="lt-sub"><i class="bi bi-telephone"></i> ' + esc(t.driver.phone) + '</span>' : '') + '</div></div>'
            + (t.driver.phone ? '<a href="tel:' + esc(t.driver.phone) + '" class="lt-btn lt-btn--call"><i class="bi bi-telephone-fill"></i> ' + esc(CFG.t.call) + '</a>' : ''),
            'indigo') : '';

        const carSec = (hasDriver && car) ? section('bi-car-front-fill', CFG.t.secCar,
            '<div class="lt-specs">'
            + spec('bi-car-front', CFG.t.secCar, [car.brand, car.model].filter(Boolean).join(' '))
            + spec('bi-credit-card-2-front', CFG.t.plate, car.plate)
            + (car.color ? spec('bi-palette', CFG.t.color, car.color) : '')
            + (car.year ? spec('bi-calendar3', CFG.t.year, car.year) : '')
            + (car.seats ? spec('bi-people', CFG.t.seats, car.seats) : '')
            + '</div>', 'amber') : '';

        const custSec = section('bi-person-fill', CFG.t.secCustomer,
            '<div class="lt-id"><span class="lt-av lt-av--c">' + esc(initial(t.customer.name || '—')) + '</span>'
            + '<div class="lt-id__b"><strong>' + esc(t.customer.name || '—') + '</strong>'
            + (t.customer.phone ? '<span class="lt-sub"><i class="bi bi-telephone"></i> ' + esc(t.customer.phone) + '</span>' : '') + '</div></div>'
            + (t.customer.phone ? '<a href="tel:' + esc(t.customer.phone) + '" class="lt-btn lt-btn--call"><i class="bi bi-telephone-fill"></i> ' + esc(CFG.t.call) + '</a>' : ''),
            'green');

        const tripSec = section('bi-info-circle-fill', CFG.t.secTrip,
            '<div class="lt-specs">'
            + (t.service ? spec('bi-tag', CFG.t.secTrip, t.service) : '')
            + spec('bi-credit-card', CFG.t.payment, t.paymentType)
            + spec('bi-wallet2', CFG.t.payStatus, t.paymentStatus)
            + spec('bi-signpost-split', CFG.t.km, t.distance + ' ' + CFG.t.km)
            + '</div>');

        return '<article class="lt-card lt-card--' + tone + '" data-id="' + t.id + '">'
            + '<header class="lt-head" data-toggle>'
            + '<span class="lt-head__id"><i class="bi bi-hash"></i>' + t.id + '</span>'
            + '<span class="lt-head__status"><i class="bi ' + (SICON[t.group] || 'bi-dot') + '"></i> ' + esc(t.statusLabel) + '</span>'
            + '<i class="bi bi-chevron-down lt-head__chev"></i>'
            + '</header>'
            + '<div class="lt-body">'
            + stepper(t.stage)
            + '<div class="lt-route"><span class="lt-pin"><i class="bi bi-geo-alt-fill"></i></span>'
            + '<span class="lt-route__t">' + esc(t.startAddress || '—') + '</span>'
            + '<i class="bi ' + arrow + ' lt-route__arr"></i>'
            + '<span class="lt-route__t">' + esc(t.endAddress || '—') + '</span></div>'
            + '<div class="lt-price"><span class="lt-price__v">' + esc(t.amount) + '</span>'
            + (t.createdAt ? '<span class="lt-price__t"><i class="bi bi-clock-history"></i> ' + esc(t.createdAt) + '</span>' : '') + '</div>'
            + '<div class="lt-driverline">' + driverline + '</div>'
            + '<div class="lt-more">'
            + driverSec + carSec + custSec + tripSec
            + '<div class="lt-actions">'
            + '<a href="' + showUrl(t) + '" class="lt-btn lt-btn--view"><i class="bi bi-eye"></i> ' + esc(CFG.t.details) + '</a>'
            + (CFG.canEdit && t.group === 'ongoing' && !t.isRide ? '<button type="button" class="lt-btn lt-btn--act" data-action="' + t.id + '"><i class="bi bi-sliders"></i> ' + esc(CFG.t.action) + '</button>' : '')
            + '</div>'
            + '</div></div></article>';
    }

    function sig(t) { return t.status + '|' + (t.driver ? t.driver.name : '') + '|' + t.amount + '|' + t.stage; }

    function reconcile(body, desired) {
        const want = new Map(desired.map(t => [String(t.id), t]));

        Array.from(body.children).forEach(node => {
            if (!node.dataset || !node.dataset.id) { node.remove(); return; }
            if (!want.has(node.dataset.id) && !node.classList.contains('is-exit')) {
                node.classList.add('is-exit');
                const done = () => { if (node.parentNode) node.remove(); };
                node.addEventListener('animationend', done, { once: true });
                setTimeout(done, 460);
            }
        });

        let prev = null;
        desired.forEach(t => {
            const id = String(t.id);
            const s = sig(t);
            let node = body.querySelector(':scope > .lt-card[data-id="' + id + '"]:not(.is-exit)');

            if (!node) {
                node = makeEl(cardHTML(t));
                if (state.open.has(id)) node.classList.add('is-open');
                node.dataset.sig = s;
                node.classList.add('is-enter');
                node.addEventListener('animationend', () => node.classList.remove('is-enter'), { once: true });
                prev ? prev.after(node) : body.prepend(node);
            } else {
                if (node.dataset.sig !== s) {
                    const open = node.classList.contains('is-open');
                    const nn = makeEl(cardHTML(t));
                    if (open) nn.classList.add('is-open');
                    nn.dataset.sig = s;
                    nn.classList.add('is-flash');
                    nn.addEventListener('animationend', () => nn.classList.remove('is-flash'), { once: true });
                    node.replaceWith(nn);
                    node = nn;
                }
                if (prev) { if (prev.nextElementSibling !== node) prev.after(node); }
                else if (body.firstElementChild !== node) { body.prepend(node); }
            }
            prev = node;
        });

        const hasReal = body.querySelector(':scope > .lt-card:not(.is-exit)');
        const ph = body.querySelector(':scope > .lt-wait');
        if (!hasReal && !desired.length) {
            if (!ph) body.insertAdjacentHTML('beforeend', '<div class="lt-wait"><span class="lt-wait__radar"><span></span><span></span><i class="bi bi-broadcast"></i></span><span class="lt-wait__txt">' + esc(CFG.t.waiting) + '</span></div>');
        } else if (ph) { ph.remove(); }
    }

    function render() {
        state.dirty = false;
        renderRail();

        const sumOngoing = state.sources.reduce((a, s) => a + (s.ongoing || 0), 0);
        document.getElementById('liveActiveCount').textContent = state.searching.length + sumOngoing;

        if (!state.sources.length) {
            cols.classList.add('is-hidden');
            emptyBox.classList.remove('is-hidden');
            return;
        }
        cols.classList.remove('is-hidden');
        emptyBox.classList.add('is-hidden');

        const byId = (a, b) => b.id - a.id;
        const searching = state.searching.slice().sort(byId);
        const ongoing = state.companyTrips.filter(t => t.group === 'ongoing').sort(byId);
        const completed = state.companyTrips.filter(t => t.group === 'completed').sort(byId);

        reconcile(bodies.searching, searching);

        if (state.loadingCompany) {
            bodies.ongoing.innerHTML = '<div class="lt-loading"><span class="trip-spin"></span></div>';
            bodies.completed.innerHTML = '<div class="lt-loading"><span class="trip-spin"></span></div>';
        } else {
            reconcile(bodies.ongoing, ongoing);
            reconcile(bodies.completed, completed);
        }

        document.querySelector('[data-count="searching"]').textContent = searching.length;
        document.querySelector('[data-count="ongoing"]').textContent = ongoing.length;
        document.querySelector('[data-count="completed"]').textContent = completed.length;
        const label = companyLabel();
        document.querySelector('[data-coname="ongoing"]').textContent = label;
        document.querySelector('[data-coname="completed"]').textContent = label;

        if (state.findId != null) {
            const id = state.findId;
            requestAnimationFrame(() => {
                const el = document.querySelector('.lt-card[data-id="' + id + '"]');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('is-flash');
                    setTimeout(() => el.classList.remove('is-flash'), 1600);
                }
            });
            state.findId = null;
        }
    }

    function scheduleRender() {
        if (state.dirty) return;
        state.dirty = true;
        requestAnimationFrame(render);
    }

    async function fetchSummary() {
        try {
            const res = await fetch(CFG.summaryUrl, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            state.searching = json.searching || [];
            state.sources = json.sources || [];
            const changed = ensureCompany();
            render();
            if (changed) fetchCompany(state.company);
        } catch (e) { console.error('summary error', e); render(); }
    }

    async function fetchCompany(key) {
        if (!key) { state.companyTrips = []; render(); return; }
        state.loadingCompany = true;
        render();
        try {
            const res = await fetch(CFG.companyUrl + '?company=' + encodeURIComponent(key), { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (state.company !== key) return;
            state.companyTrips = json.trips || [];
        } catch (e) {
            console.error('company error', e);
            if (state.company === key) state.companyTrips = [];
        } finally {
            if (state.company === key) { state.loadingCompany = false; render(); }
        }
    }

    function selectCompany(key) {
        if (key === state.company) return;
        state.company = key;
        state.companyTrips = [];
        fetchCompany(key);
    }

    function refreshSummarySoon() {
        if (state.sumTimer) return;
        state.sumTimer = setTimeout(() => { state.sumTimer = null; fetchSummary(); }, 3500);
    }

    function upsertSearching(o) {
        const i = state.searching.findIndex(t => t.id === o.id);
        if (i !== -1) state.searching[i] = o; else state.searching.unshift(o);
    }
    function dropEverywhere(id) {
        let k = state.searching.findIndex(t => t.id === id); if (k !== -1) state.searching.splice(k, 1);
        k = state.companyTrips.findIndex(t => t.id === id); if (k !== -1) state.companyTrips.splice(k, 1);
    }

    function onUpsert(o) {
        if (!o || !o.id) return;
        if (o.group === 'cancelled') { dropEverywhere(o.id); scheduleRender(); refreshSummarySoon(); return; }

        if (o.group === 'pending') {
            const c = state.companyTrips.findIndex(t => t.id === o.id);
            if (c !== -1) state.companyTrips.splice(c, 1);
            upsertSearching(o);
        } else {
            const s = state.searching.findIndex(t => t.id === o.id);
            if (s !== -1) state.searching.splice(s, 1);
            if (srcKey(o) === state.company) {
                const i = state.companyTrips.findIndex(t => t.id === o.id);
                if (i !== -1) state.companyTrips[i] = o; else state.companyTrips.unshift(o);
            } else {
                dropEverywhere(o.id);
            }
        }
        scheduleRender();
        refreshSummarySoon();
    }

    function onRemove(o) {
        if (!o || !o.id) return;
        dropEverywhere(o.id);
        scheduleRender();
        refreshSummarySoon();
    }

    function startPolling() { if (state.poll) return; setStatus('polling'); state.poll = setInterval(fetchSummary, 12000); }
    function stopPolling() { if (state.poll) { clearInterval(state.poll); state.poll = null; } }

    function connectRealtime() {
        if (!CFG.realtimeEnabled || typeof io === 'undefined' || !CFG.realtimeUrl) { startPolling(); return; }
        let socket;
        try { socket = io(CFG.realtimeUrl, { transports: ['websocket', 'polling'], reconnectionAttempts: 5 }); }
        catch (e) { startPolling(); return; }
        socket.on('connect', function () { stopPolling(); setStatus('live'); socket.emit('subscribe', CFG.channel); });
        socket.on(CFG.channel + ':order-board', function (data) {
            if (!data) return;
            if (data.action === 'remove') onRemove(data.order); else onUpsert(data.order);
        });
        socket.on('disconnect', function () { setStatus('offline'); startPolling(); });
        socket.on('connect_error', function () { startPolling(); });
        socket.io.on('reconnect_failed', function () { setStatus('offline'); startPolling(); });
        setInterval(fetchSummary, 30000);
    }

    rail.addEventListener('click', e => {
        const btn = e.target.closest('[data-company]');
        if (btn) selectCompany(btn.dataset.company);
    });

    const railSearch = document.getElementById('railSearch');
    if (railSearch) railSearch.addEventListener('input', e => { state.query = e.target.value; renderRail(); });

    cols.addEventListener('click', e => {
        const act = e.target.closest('[data-action]');
        if (act) { openAction(parseInt(act.dataset.action, 10)); return; }
        const head = e.target.closest('[data-toggle]');
        if (!head) return;
        const card = head.closest('.lt-card');
        const id = card.dataset.id;
        card.classList.toggle('is-open');
        if (card.classList.contains('is-open')) state.open.add(id); else state.open.delete(id);
    });

    async function doFind() {
        const inp = document.getElementById('tripFind');
        const id = parseInt(inp.value, 10);
        if (!id || id < 1) return;
        inp.classList.remove('is-bad');
        try {
            const res = await fetch(CFG.findUrl + '?id=' + id, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!json.found || !json.trip) { inp.classList.add('is-bad'); return; }
            const trip = json.trip;
            if (trip.group === 'pending') {
                const i = state.searching.findIndex(t => t.id === trip.id);
                if (i !== -1) state.searching[i] = trip; else state.searching.unshift(trip);
                state.findId = trip.id;
                render();
            } else {
                state.findId = trip.id;
                const key = trip.source ? trip.source.key : 'fleet';
                if (key === state.company) {
                    const i = state.companyTrips.findIndex(t => t.id === trip.id);
                    if (i !== -1) state.companyTrips[i] = trip; else state.companyTrips.unshift(trip);
                    render();
                } else {
                    selectCompany(key);
                }
            }
        } catch (e) { console.error('find error', e); inp.classList.add('is-bad'); }
    }

    const actModal = document.getElementById('liveActionModal');
    const ACTS = {
        hold:            { label: CFG.t.actHold,   icon: 'bi-pause-circle-fill', cls: 'lt-btn--hold' },
        complete_paid:   { label: CFG.t.actPaid,   icon: 'bi-cash-coin',         cls: 'lt-btn--done' },
        complete_unpaid: { label: CFG.t.actUnpaid, icon: 'bi-check2-circle',     cls: 'lt-btn--done2' },
        cancel:          { label: CFG.t.actCancel, icon: 'bi-x-octagon-fill',    cls: 'lt-btn--cancel' },
    };

    function openAction(id) {
        if (!actModal) return;
        state.actionId = id;
        state.actionType = null;
        document.getElementById('actRef').textContent = '#' + id;
        document.getElementById('actChoices').classList.remove('is-hidden');
        document.getElementById('actConfirm').classList.add('is-hidden');
        actModal.classList.add('open');
    }

    function makeChallenge() {
        if (Math.random() < 0.5) {
            const a = 2 + Math.floor(Math.random() * 8);
            const b = 2 + Math.floor(Math.random() * 8);
            const mul = Math.random() < 0.4;
            const ans = mul ? a * b : a + b;
            return { q: CFG.t.chMath.replace(':op', a + (mul ? ' × ' : ' + ') + b), ans: String(ans) };
        }
        return { q: CFG.t.chWord.replace(':word', CFG.t.chWordVal), ans: CFG.t.chWordVal };
    }

    function chooseAction(act) {
        const meta = ACTS[act];
        if (!meta) return;
        state.actionType = act;
        document.getElementById('actChosen').innerHTML = '<i class="bi ' + meta.icon + '"></i> ' + esc(meta.label);

        const reason = document.getElementById('actReason');
        reason.value = ''; reason.classList.remove('is-bad');
        reason.classList.toggle('is-hidden', act !== 'cancel');

        const ch = makeChallenge();
        state.challengeAns = ch.ans;
        document.getElementById('actChQ').textContent = ch.q;
        const inp = document.getElementById('actChIn');
        inp.value = ''; inp.classList.remove('is-bad');

        document.getElementById('actGo').className = 'lt-btn ' + meta.cls;
        document.getElementById('actChoices').classList.add('is-hidden');
        document.getElementById('actConfirm').classList.remove('is-hidden');
        inp.focus();
    }

    function submitAction() {
        if (!state.actionType) return;
        let ok = true;
        const reason = document.getElementById('actReason');
        if (state.actionType === 'cancel' && !reason.value.trim()) { reason.classList.add('is-bad'); ok = false; }
        const inp = document.getElementById('actChIn');
        if (inp.value.trim().toLowerCase() !== String(state.challengeAns).toLowerCase()) { inp.classList.add('is-bad'); ok = false; }
        if (!ok) return;
        postAction(state.actionType, reason.value.trim());
    }

    async function postAction(action, reason) {
        if (state.posting || !CFG.actionBase || state.actionId == null) return;
        state.posting = true;
        const btn = document.getElementById('actGo');
        const old = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<span class="trip-spin"></span>';
        try {
            const res = await fetch(CFG.actionBase.replace('__ID__', state.actionId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CFG.csrf },
                body: JSON.stringify({ action: action, reason: reason || null }),
            });
            const json = await res.json();
            if (json.ok) {
                actModal.classList.remove('open');
                if (json.trip) onUpsert(json.trip); else onRemove({ id: state.actionId });
                refreshSummarySoon();
            }
        } catch (e) { console.error('action error', e); }
        finally { state.posting = false; btn.disabled = false; btn.innerHTML = old; }
    }

    if (actModal) {
        document.getElementById('actChoices').querySelectorAll('[data-act]').forEach(b =>
            b.addEventListener('click', () => chooseAction(b.dataset.act)));
        document.getElementById('actGo').addEventListener('click', submitAction);
        document.getElementById('actChIn').addEventListener('keydown', e => { if (e.key === 'Enter') submitAction(); });
        document.getElementById('actBack').addEventListener('click', () => {
            document.getElementById('actConfirm').classList.add('is-hidden');
            document.getElementById('actChoices').classList.remove('is-hidden');
        });
        actModal.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => actModal.classList.remove('open')));
        actModal.addEventListener('click', e => { if (e.target === actModal) actModal.classList.remove('open'); });
    }

    document.getElementById('tripFindBtn').addEventListener('click', doFind);
    document.getElementById('tripFind').addEventListener('keydown', e => { if (e.key === 'Enter') doFind(); });

    (async function init() {
        await fetchSummary();
        if (state.company) await fetchCompany(state.company);
        connectRealtime();
    })();
})();
</script>
@endpush
