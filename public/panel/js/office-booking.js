(function () {
    var form = document.getElementById('obkForm');
    if (!form) return;
    var CFG = window.OBK || { t: {} };

    var step = 0, maxStep = 3;
    var map = null, markers = {}, active = 'pickup';
    var quoted = { fare: null, currency: '' };

    var el = function (id) { return document.getElementById(id); };
    var q = function (s) { return form.querySelector(s); };
    var qa = function (s) { return form.querySelectorAll(s); };

    var panels = qa('.ob-panel');
    var steps = qa('.ob-step');
    var bar = document.getElementById('obBar');
    var btnPrev = q('[data-prev]'), btnNext = q('[data-next]'), btnSubmit = q('[data-submit]');

    /* ---------- service chips ---------- */
    function pickTariff(chip) {
        qa('.ob-chip').forEach(function (c) { c.classList.remove('is-on'); });
        chip.classList.add('is-on');
        var parts = (chip.getAttribute('data-tariff') || '').split('::');
        el('f_service').value = parts[0] || 'ride';
        el('f_class').value = parts[1] || '';
        quoted.currency = chip.getAttribute('data-currency') || '';
        quoted.fare = null;
        updateSummary();
    }
    qa('.ob-chip').forEach(function (c) { c.addEventListener('click', function () { pickTariff(c); }); });
    if (qa('.ob-chip').length) pickTariff(qa('.ob-chip')[0]);

    /* ---------- passenger stepper ---------- */
    qa('[data-pax]').forEach(function (b) {
        b.addEventListener('click', function () {
            var inp = el('f_pax'); var v = parseInt(inp.value, 10) || 1;
            v += (b.getAttribute('data-pax') === '+' ? 1 : -1);
            inp.value = Math.max(1, Math.min(20, v));
        });
    });

    /* ---------- validation helpers ---------- */
    function fieldError(key, msg) {
        var box = q('[data-err="' + key + '"]');
        if (box) { box.textContent = msg || ''; box.classList.toggle('show', !!msg); }
    }
    function markBad(input) { var w = input.closest('.ob-inp') || input; w.classList.add('is-bad'); setTimeout(function () { w.classList.remove('is-bad'); }, 500); }
    function phoneOk() { var v = (el('f_phone').value || '').replace(/[^\d+]/g, ''); return v.replace(/\D/g, '').length >= 6; }
    function hasTrip() { return el('f_plat').value && el('f_plng').value && el('f_dlat').value && el('f_dlng').value; }

    function validateStep() {
        if (step === 0) {
            if (!phoneOk()) { fieldError('phone', CFG.t.needPhone); markBad(el('f_phone')); return false; }
            fieldError('phone', '');
        }
        if (step === 1) {
            if (!el('f_service').value) return false;
            if (!hasTrip()) { fieldError('trip', CFG.t.needTrip); return false; }
            fieldError('trip', '');
        }
        if (step === 3 && el('f_mode').value === 'driver' && !el('f_driver').value) {
            fieldError('driver', CFG.t.needDriver); return false;
        }
        return true;
    }

    /* ---------- step navigation ---------- */
    function showStep(i) {
        step = Math.max(0, Math.min(maxStep, i));
        panels.forEach(function (p) { p.classList.toggle('is-active', +p.getAttribute('data-panel') === step); });
        steps.forEach(function (d) {
            var n = +d.getAttribute('data-dot');
            d.classList.toggle('is-active', n === step);
            d.classList.toggle('is-done', n < step);
            d.classList.toggle('is-todo', n > step);
        });
        if (bar) bar.style.width = (step / maxStep * 100) + '%';
        btnPrev.style.visibility = step === 0 ? 'hidden' : 'visible';
        btnNext.style.display = step === maxStep ? 'none' : '';
        btnSubmit.style.display = step === maxStep ? '' : 'none';
        if (step === 2) fetchQuote();
        updateSummary();
    }
    btnNext.addEventListener('click', function () { if (validateStep()) showStep(step + 1); });
    btnPrev.addEventListener('click', function () { showStep(step - 1); });
    // allow clicking a completed step to jump back
    steps.forEach(function (d) { d.addEventListener('click', function () { var n = +d.getAttribute('data-dot'); if (n < step) showStep(n); }); });

    el('f_phone').addEventListener('input', function () { if (phoneOk()) fieldError('phone', ''); updateSummary(); });
    el('f_phone').addEventListener('blur', updateSummary);

    /* ---------- quote ---------- */
    function fetchQuote() {
        if (!hasTrip()) return;
        var box = q('[data-fare-box]'), spin = q('[data-fare-loading]'), err = q('[data-fare-err]');
        box.style.display = 'none'; err.style.display = 'none'; spin.style.display = '';
        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        fetch(CFG.quoteUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: JSON.stringify({
                office_id: CFG.officeId, service: el('f_service').value, service_class: el('f_class').value,
                pickup_lat: el('f_plat').value, pickup_lng: el('f_plng').value,
                dropoff_lat: el('f_dlat').value, dropoff_lng: el('f_dlng').value
            })
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
          .then(function (res) {
            spin.style.display = 'none';
            if (!res.ok || !res.j.data) { err.textContent = (res.j && res.j.error) || 'Could not price this trip'; err.style.display = ''; return; }
            var d = res.j.data;
            quoted.fare = d.suggested_fare_minor; quoted.currency = d.currency_code;
            q('[data-fare-amt]').textContent = (d.suggested_fare_minor / 100).toFixed(2);
            q('[data-fare-cur]').textContent = d.currency_code + ' · ' + (d.distance_m / 1000).toFixed(1) + ' km';
            box.style.display = '';
            if (!el('f_manualToggle').checked && !el('f_fare').value) el('f_fare').placeholder = (d.suggested_fare_minor / 100).toFixed(2);
            updateSummary();
        }).catch(function () { spin.style.display = 'none'; err.textContent = 'Network error'; err.style.display = ''; });
    }

    var useBtn = q('[data-fare-use]');
    if (useBtn) useBtn.addEventListener('click', function () {
        if (quoted.fare == null) return;
        el('f_manualToggle').checked = true; el('f_manualWrap').style.display = '';
        el('f_fare').value = (quoted.fare / 100).toFixed(2); updateSummary();
    });

    el('f_manualToggle').addEventListener('change', function () {
        el('f_manualWrap').style.display = this.checked ? '' : 'none';
        if (!this.checked) el('f_fare').value = '';
        updateSummary();
    });
    el('f_fare').addEventListener('input', updateSummary);

    /* ---------- payment / assign option cards ---------- */
    qa('[data-pay]').forEach(function (b) {
        b.addEventListener('click', function () {
            qa('[data-pay]').forEach(function (x) { x.classList.remove('is-on'); });
            b.classList.add('is-on');
            el('f_payment').value = b.getAttribute('data-pay');
            updateSummary();
        });
    });
    qa('[data-assign]').forEach(function (b) {
        b.addEventListener('click', function () {
            qa('[data-assign]').forEach(function (x) { x.classList.remove('is-on'); });
            b.classList.add('is-on');
            var mode = b.getAttribute('data-assign');
            el('f_mode').value = mode;
            q('[data-drivers]').style.display = mode === 'driver' ? '' : 'none';
            if (mode === 'broadcast') { el('f_driver').value = ''; qa('input[name="_driver_pick"]').forEach(function (r) { r.checked = false; }); qa('.ob-driver').forEach(function (l) { l.classList.remove('is-picked'); }); fieldError('driver', ''); }
            updateSummary();
        });
    });

    /* ---------- driver pick + search ---------- */
    qa('input[name="_driver_pick"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            el('f_driver').value = this.value;
            qa('.ob-driver').forEach(function (l) { l.classList.remove('is-picked'); });
            this.closest('.ob-driver').classList.add('is-picked');
            fieldError('driver', '');
            updateSummary();
        });
    });
    var search = document.getElementById('obDriverSearch');
    if (search) search.addEventListener('input', function () {
        var term = this.value.toLowerCase();
        qa('.ob-driver').forEach(function (l) {
            var hit = (l.getAttribute('data-name') || '').indexOf(term) > -1 || (l.getAttribute('data-plate') || '').indexOf(term) > -1;
            l.style.display = hit ? '' : 'none';
        });
    });

    /* ---------- live summary ---------- */
    function setSum(key, val) {
        var e = document.querySelector('[data-sum="' + key + '"]'); if (!e) return;
        e.textContent = val || CFG.t.dash; e.classList.toggle('set', !!val);
    }
    function updateSummary() {
        var name = (form.querySelector('[name="name"]') || {}).value;
        var phone = el('f_phone').value;
        setSum('customer', (name ? name + ' · ' : '') + (phone || ''));
        var route = (el('f_ptitle').value || (el('f_plat').value ? '📍' : '')) + (el('f_dtitle').value || el('f_dlat').value ? ' → ' + (el('f_dtitle').value || '📍') : '');
        setSum('route', hasTrip() || el('f_ptitle').value ? route : '');
        var chip = form.querySelector('.ob-chip.is-on');
        setSum('service', chip ? chip.textContent.trim() + (el('f_pax').value > 1 ? ' · ' + el('f_pax').value + '👤' : '') : '');
        var fare = el('f_fare').value ? parseFloat(el('f_fare').value).toFixed(2) : (quoted.fare != null ? (quoted.fare / 100).toFixed(2) : null);
        var pay = el('f_payment').value === 'office_wallet' ? CFG.t.payWallet : CFG.t.payCash;
        setSum('price', fare ? fare + ' ' + (quoted.currency || '') + ' · ' + pay : '');
        var drv;
        if (el('f_mode').value === 'broadcast') drv = CFG.t.broadcast;
        else { var picked = form.querySelector('.ob-driver.is-picked strong'); drv = picked ? picked.textContent : ''; }
        setSum('driver', drv);
    }

    /* ---------- map + places ---------- */
    qa('[data-set]').forEach(function (b) {
        b.addEventListener('click', function () {
            active = b.getAttribute('data-set');
            qa('[data-set]').forEach(function (x) { x.classList.remove('is-on'); });
            b.classList.add('is-on');
            var hint = q('[data-map-hint]');
            if (hint) hint.innerHTML = '<i class="bi bi-hand-index"></i> ' + (active === 'pickup' ? CFG.t.setPickup : CFG.t.setDrop);
        });
    });
    qa('[data-manual]').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var mapIds = { plat: 'f_plat', plng: 'f_plng', dlat: 'f_dlat', dlng: 'f_dlng' };
            el(mapIds[inp.getAttribute('data-manual')]).value = inp.value; updateSummary();
        });
    });

    function setPoint(kind, lat, lng, title) {
        el(kind === 'pickup' ? 'f_plat' : 'f_dlat').value = lat;
        el(kind === 'pickup' ? 'f_plng' : 'f_dlng').value = lng;
        if (title) el(kind === 'pickup' ? 'f_ptitle' : 'f_dtitle').value = title;
        if (map && window.google) {
            if (markers[kind]) markers[kind].setPosition({ lat: lat, lng: lng });
            else markers[kind] = new google.maps.Marker({ position: { lat: lat, lng: lng }, map: map, draggable: true, label: kind === 'pickup' ? 'A' : 'B' });
            markers[kind].addListener('dragend', function (e) { setPoint(kind, e.latLng.lat(), e.latLng.lng(), null); });
        }
        if (hasTrip()) fieldError('trip', '');
        updateSummary();
    }

    window.obkInitMap = function () {
        if (!CFG.mapReady) return;
        map = new google.maps.Map(el('obkMap'), { center: CFG.center, zoom: 12, streetViewControl: false, mapTypeControl: false, fullscreenControl: true });
        map.addListener('click', function (e) { setPoint(active, e.latLng.lat(), e.latLng.lng(), null); });
        ['pickup', 'dropoff'].forEach(function (kind) {
            var inp = el(kind === 'pickup' ? 'f_ptitle' : 'f_dtitle');
            var ac = new google.maps.places.Autocomplete(inp, { fields: ['geometry', 'name', 'formatted_address'] });
            ac.bindTo('bounds', map);
            ac.addListener('place_changed', function () {
                var p = ac.getPlace();
                if (!p.geometry) return;
                var loc = p.geometry.location;
                setPoint(kind, loc.lat(), loc.lng(), p.name || p.formatted_address);
                map.panTo(loc); map.setZoom(14);
            });
        });
    };

    /* ---------- submit ---------- */
    form.addEventListener('submit', function (e) {
        if (!hasTrip()) { e.preventDefault(); showStep(1); fieldError('trip', CFG.t.needTrip); return; }
        if (el('f_mode').value === 'driver' && !el('f_driver').value) { e.preventDefault(); showStep(3); fieldError('driver', CFG.t.needDriver); return; }
        var ov = document.getElementById('obSuccess'); if (ov) ov.classList.add('show');
    });

    showStep(0);
    updateSummary();
})();
