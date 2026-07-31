/**
 * The panel's live ear.
 *
 * Six domain events were reaching the browser over the gateway and being thrown
 * away: nothing on any page listened for order.created, dispatch.offer_created,
 * subscription.past_due, rating.received, wallet.payout or ride.released. A new
 * order landed, a subscriber's card failed, a payout was requested — and the
 * desk learnt about it only when somebody happened to reload.
 *
 * This turns each of them into a bell item (with the unread badge) and, for the
 * ones a human should react to, a toast. Offers are deliberately silent: a
 * matching wave fires one per driver asked, so toasting them would bury the
 * events that matter.
 *
 * Pages that render their own live view still get the raw `fleet:rt:*` events —
 * this layer only adds the chrome, and never replaces a page's own handler.
 */
(function () {
    var L = (document.documentElement.getAttribute('lang') || 'ar').indexOf('ar') === 0;
    var t = function (ar, en) { return L ? ar : en; };

    function money(minor, currency) {
        var n = (Number(minor || 0) / 100).toFixed(2);
        return currency ? n + ' ' + currency : n;
    }

    function ref(d) {
        return d && d.booking_id ? '#' + d.booking_id : '';
    }

    var EVENTS = {
        'order.created': function (d) {
            return {
                icon: 'bi-bag-plus', tone: 'primary', toast: true,
                title: t('طلب جديد', 'New order'),
                body: [ref(d), d.service_class, d.pickup_title].filter(Boolean).join(' · '),
                link: 'bookings.live'
            };
        },
        'dispatch.offer_created': function (d) {
            return {
                icon: 'bi-broadcast', tone: 'gray', toast: false,
                title: t('عرض أُرسل لسائق', 'Offer sent to a driver'),
                body: [ref(d), d.distance_m ? Math.round(d.distance_m) + ' m' : ''].filter(Boolean).join(' · '),
                link: 'bookings.live'
            };
        },
        'subscription.past_due': function (d) {
            return {
                icon: 'bi-exclamation-octagon', tone: 'danger', toast: true,
                title: t('فشل دفع اشتراك', 'Subscription payment failed'),
                body: [d.plan_key, d.office_id ? t('مكتب', 'office') + ' #' + d.office_id : ''].filter(Boolean).join(' · '),
                link: 'subscriptions.index'
            };
        },
        'wallet.payout': function (d) {
            return {
                icon: 'bi-cash-coin', tone: 'warning', toast: true,
                title: t('طلب سحب', 'Payout requested'),
                body: money(d.amount, d.currency_code),
                link: 'payouts.index'
            };
        },
        'ride.released': function (d) {
            return {
                icon: 'bi-check2-circle', tone: 'success', toast: true,
                title: t('تسوية رحلة', 'Ride settled'),
                body: [ref(d), money(d.total_minor, null), d.payment_method].filter(Boolean).join(' · '),
                link: 'wallet.transactions'
            };
        },
        'rating.received': function (d) {
            var low = Number(d.stars || 0) <= 2;
            return {
                icon: low ? 'bi-star' : 'bi-star-fill', tone: low ? 'danger' : 'primary', toast: low,
                title: low ? t('تقييم منخفض', 'Low rating') : t('تقييم جديد', 'New rating'),
                body: [(d.stars || '?') + '★', ref(d)].filter(Boolean).join(' · '),
                link: 'ride-ratings.index'
            };
        }
    };

    function bellList() { return document.querySelector('.panel-bell__list'); }

    function bump() {
        var badge = document.querySelector('.panel-bell__badge');
        var btn = document.querySelector('.panel-bell__btn');
        if (!btn) { return; }
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'panel-bell__badge';
            badge.textContent = '0';
            btn.appendChild(badge);
        }
        var n = parseInt(String(badge.textContent).replace(/\D/g, ''), 10) || 0;
        n = n + 1;
        badge.textContent = n > 9 ? '9+' : String(n);
    }

    function prepend(item) {
        var list = bellList();
        if (!list) { return; }

        var empty = list.querySelector('.panel-bell__empty');
        if (empty) { empty.remove(); }

        var el = document.createElement(item.href ? 'a' : 'div');
        el.className = 'panel-bell__item is-unread';
        if (item.href) { el.href = item.href; }
        el.innerHTML =
            '<span class="panel-bell__icon p-badge--' + item.tone + '"><i class="bi ' + item.icon + '"></i></span>' +
            '<span class="panel-bell__body"><strong></strong><span></span><time>' + t('الآن', 'now') + '</time></span>';
        el.querySelector('strong').textContent = item.title;
        el.querySelector('.panel-bell__body span').textContent = item.body || '';
        list.insertBefore(el, list.firstChild);

        while (list.children.length > 12) { list.removeChild(list.lastChild); }
    }

    function toast(item) {
        var host = document.getElementById('panelToasts');
        if (!host) {
            host = document.createElement('div');
            host.id = 'panelToasts';
            host.className = 'panel-toasts';
            document.body.appendChild(host);
        }

        var el = document.createElement(item.href ? 'a' : 'div');
        el.className = 'panel-toast panel-toast--' + item.tone;
        if (item.href) { el.href = item.href; }
        el.innerHTML = '<i class="bi ' + item.icon + '"></i><span><strong></strong><small></small></span>';
        el.querySelector('strong').textContent = item.title;
        el.querySelector('small').textContent = item.body || '';
        host.appendChild(el);

        window.setTimeout(function () { el.classList.add('is-out'); }, 6000);
        window.setTimeout(function () { el.remove(); }, 6600);
    }

    var routes = window.FLEET_PANEL_ROUTES || {};

    Object.keys(EVENTS).forEach(function (type) {
        window.addEventListener('fleet:rt:' + type, function (e) {
            var data = (e.detail && e.detail.data) || {};
            var item;
            try {
                item = EVENTS[type](data);
            } catch (err) {
                return;
            }
            item.href = routes[item.link] || null;
            bump();
            prepend(item);
            if (item.toast) { toast(item); }
        });
    });
})();
