/**
 * Panel live layer — connects to the NEW rt:* realtime gateway (port 6002) and
 * re-broadcasts every incoming domain event as a DOM CustomEvent so any panel
 * page can listen without owning a socket. Runs ALONGSIDE the legacy order-board
 * socket (port 3000) used by the home/live-board pages — the two never touch.
 *
 * Config comes from window.FLEET_RT (set server-side by the realtime partial):
 *   { url, token, country, channels: [ "sy.office.5" | "sy.admin" ] }
 *
 * Pages consume via:
 *   window.addEventListener('fleet:rt', e => e.detail = { channel, event, data })
 *   window.addEventListener('fleet:rt:support.message_created', e => e.detail = { channel, data })
 */
(function () {
    var cfg = window.FLEET_RT;
    if (!cfg || !cfg.url || typeof io === 'undefined') {
        return;
    }

    var socket = io(cfg.url, {
        transports: ['websocket', 'polling'],
        auth: { token: cfg.token, country: cfg.country },
        reconnectionAttempts: Infinity,
        reconnectionDelay: 1500
    });

    var api = { socket: socket, connected: false };
    window.FleetRealtime = api;

    function subscribeAll() {
        (cfg.channels || []).forEach(function (ch) {
            socket.emit('subscribe', ch, function () {});
        });
    }

    socket.on('connect', function () {
        api.connected = true;
        subscribeAll();
        window.dispatchEvent(new CustomEvent('fleet:rt:connect'));
    });

    socket.on('disconnect', function () {
        api.connected = false;
        window.dispatchEvent(new CustomEvent('fleet:rt:disconnect'));
    });

    // Re-subscribe after a reconnect (rooms are per-connection on the gateway).
    socket.on('reconnect', subscribeAll);

    // The gateway emits every event as "{channel}:{eventType}" with the payload
    // as the single argument. onAny catches them all and fans them out.
    if (typeof socket.onAny === 'function') {
        socket.onAny(function (name, data) {
            var idx = String(name).lastIndexOf(':');
            if (idx === -1) {
                return;
            }
            var channel = name.slice(0, idx);
            var event = name.slice(idx + 1);
            var detail = { channel: channel, event: event, data: data };
            window.dispatchEvent(new CustomEvent('fleet:rt', { detail: detail }));
            window.dispatchEvent(new CustomEvent('fleet:rt:' + event, { detail: { channel: channel, data: data } }));
        });
    }
})();
