// FleetOS realtime gateway — core wiring, extracted from server.js so it can be
// driven in tests with injected fakes (authorize fn + redis clients) instead of
// a live PHP app and Redis. server.js remains the production entrypoint and
// wires the REAL dependencies; behaviour here is identical to the original
// inline handlers.

const express = require('express');
const http = require('http');

/**
 * Validate + parse an inbound `driver.location` payload. Pure (no I/O) so the
 * full rejection matrix is unit-testable. Order of checks is significant and
 * mirrors the production contract:
 *   bad_payload → not_subscribed → bad_coords → bad_channel → blank_region.
 *
 * @param {*} payload the raw socket payload
 * @param {(channel:string)=>boolean} hasRoom is the socket joined to `channel`?
 * @returns {{ok:true,channel,region,driverId,lat,lng} | {ok:false,reason:string}}
 */
function parseLocation(payload, hasRoom) {
    if (!payload || typeof payload !== 'object') {
        return { ok: false, reason: 'bad_payload' };
    }

    const channel = payload.channel;
    if (typeof channel !== 'string' || !hasRoom(channel)) {
        return { ok: false, reason: 'not_subscribed' };
    }

    const lat = Number(payload.lat);
    const lng = Number(payload.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return { ok: false, reason: 'bad_coords' };
    }

    // channel = `{region}.driver.{id}`
    const parts = channel.split('.');
    if (parts.length !== 3 || parts[1] !== 'driver' || !/^\d+$/.test(parts[2])) {
        return { ok: false, reason: 'bad_channel' };
    }

    const region = parts[0].toLowerCase();
    const driverId = parts[2];

    // A blank region still splits into 3 parts (".driver.7"), and would write to
    // `fleet:geo:` — a namespace no dispatch search ever reads. Reject rather
    // than silently discard while acking success.
    if (region === '') {
        return { ok: false, reason: 'blank_region' };
    }

    return { ok: true, channel, region, driverId, lat, lng };
}

function reply(ack, authorized) {
    if (typeof ack === 'function') {
        ack({ authorized });
    }
}

/**
 * Build the express app + Socket.IO server + all handlers.
 *
 * @param {object} deps
 * @param {(token:string,country:string|undefined,channel:string)=>Promise<boolean>} deps.authorize
 * @param {object} deps.redisPub  ioredis-like: multi().geoadd().set().exec(), publish()
 * @param {object} [deps.redisSub] ioredis-like: psubscribe(), on('pmessage')
 * @param {(...a:any[])=>void} [deps.log]
 * @param {number} [deps.locationTtl]
 * @param {string} [deps.channelPrefix]
 * @param {string|string[]} [deps.corsOrigin]
 * @returns {{app, server, io}}
 */
function createGateway({
    authorize,
    redisPub,
    redisSub,
    log = () => {},
    locationTtl = 3600,
    channelPrefix = 'rt:',
    corsOrigin = '*',
}) {
    const app = express();
    const server = http.createServer(app);

    app.get('/health', (req, res) => res.json({ ok: true, ts: Date.now() }));

    const io = require('socket.io')(server, {
        cors: { origin: corsOrigin, methods: ['GET', 'POST'] },
        pingInterval: 25000,
        pingTimeout: 20000,
    });

    // Redis → Socket.IO relay: every `rt:{room}` message becomes `room:event`
    // for the joined room, unless it was mirrored from a socket-direct event
    // (socket:true), which would double-deliver.
    if (redisSub) {
        redisSub.psubscribe(channelPrefix + '*', (err, count) => {
            if (err) {
                console.error('[fleet-rt] psubscribe failed:', err.message);
                return;
            }
            console.log(`[fleet-rt] subscribed to ${count} pattern(s): ${channelPrefix}*`);
        });

        redisSub.on('pmessage', (pattern, channel, raw) => {
            let message;
            try {
                message = JSON.parse(raw);
            } catch (e) {
                return;
            }

            if (!message || message.socket === true) {
                return;
            }

            const room = channel.slice(channelPrefix.length);
            io.to(room).emit(room + ':' + message.event, message.data);
            log('RELAY(redis)', room + ':' + message.event, JSON.stringify(message.data || {}));
        });
    }

    io.on('connection', (socket) => {
        const auth = socket.handshake.auth || {};
        const token = auth.token;
        const country = auth.country || socket.handshake.headers['x-country'];

        if (!token) {
            socket.emit('unauthorized', { reason: 'missing_token' });
            socket.disconnect(true);
            return;
        }

        log('CONNECT', socket.id);

        socket.on('subscribe', async (channel, ack) => {
            const allowed = await authorize(token, country, channel);

            if (allowed) {
                socket.join(channel);
            }

            log('SUBSCRIBE', socket.id, channel, allowed ? 'OK' : 'DENIED');
            reply(ack, allowed);
        });

        socket.on('unsubscribe', (channel) => {
            if (typeof channel === 'string') {
                socket.leave(channel);
                log('UNSUBSCRIBE', socket.id, channel);
            }
        });

        socket.on('disconnect', (reason) => log('DISCONNECT', socket.id, reason));

        // Live driver GPS — relayed only to a channel the socket is already
        // authorized for (joined via `subscribe`), so a driver publishes strictly
        // to its own private `driver.{id}` channel.
        socket.on('driver.location', async (payload, ack) => {
            const reject = (reason) => { if (typeof ack === 'function') ack({ stored: false, reason }); };

            const parsed = parseLocation(payload, (ch) => socket.rooms.has(ch));
            if (!parsed.ok) {
                if (parsed.reason === 'blank_region') {
                    log('LOCATION-REJECT', socket.id, payload && payload.channel, 'blank_region');
                }
                return reject(parsed.reason);
            }

            const { channel, region, driverId, lat, lng } = parsed;
            const frame = { channel, lat, lng, ts: Date.now() };

            try {
                // Persist FIRST, then acknowledge — a positive ack truthfully means
                // "the server has this position". Contract shared with PHP
                // DriverLocationStore: geo set for radius search + per-driver TTL key.
                await redisPub
                    .multi()
                    .geoadd(`fleet:geo:${region}`, lng, lat, driverId)
                    .set(`fleet:loc:${region}:${driverId}`, JSON.stringify({ lat, lng, ts: Math.floor(Date.now() / 1000) }), 'EX', locationTtl)
                    .exec();
            } catch (e) {
                log('LOCATION-ERR', socket.id, channel, e.message);
                return reject('store_failed');
            }

            io.to(channel).emit(channel + ':driver.location', frame);
            // Mirror onto the bus for observers (socket:true → the relay skips it).
            redisPub.publish(channelPrefix + channel, JSON.stringify({ event: 'driver.location', data: frame, socket: true }));
            log('LOCATION', socket.id, channel, 'lat=' + lat, 'lng=' + lng, 'stored');

            if (typeof ack === 'function') {
                ack({ stored: true, ts: frame.ts });
            }
        });
    });

    return { app, server, io };
}

module.exports = { createGateway, parseLocation, reply };
