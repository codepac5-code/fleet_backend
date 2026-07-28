// Integration + unit coverage for the realtime gateway (gateway.js). Runs a
// REAL in-process Socket.IO server via createGateway() with injected fakes for
// Redis + the PHP authorize check, and drives it with a real socket.io-client.
// No live Redis or PHP app required.
//
//   node --test        (from realtime-gateway/)

const { test } = require('node:test');
const assert = require('node:assert/strict');
const { EventEmitter } = require('node:events');
const ioClient = require('socket.io-client');

const { createGateway, parseLocation } = require('../gateway');

// ── Fakes ──────────────────────────────────────────────────────────

function makeRedisPub({ failExec = false } = {}) {
    const calls = { geoadd: [], set: [], publish: [], exec: 0 };
    const chain = {
        geoadd: (...a) => { calls.geoadd.push(a); return chain; },
        set: (...a) => { calls.set.push(a); return chain; },
        exec: async () => { calls.exec++; if (failExec) throw new Error('redis down'); return []; },
    };
    return { calls, multi: () => chain, publish: (...a) => { calls.publish.push(a); } };
}

function makeRedisSub() {
    const e = new EventEmitter();
    // The gateway calls psubscribe(pattern, cb); ack success with 1 pattern.
    e.psubscribe = (_pattern, cb) => { if (typeof cb === 'function') cb(null, 1); };
    return e;
}

// Start a gateway on an ephemeral port. `authorize` decides which channels a
// token may join (defaults to allow-all so tests opt into denial explicitly).
function startGateway({ authorize, pubOpts } = {}) {
    const redisPub = makeRedisPub(pubOpts);
    const redisSub = makeRedisSub();
    const authCalls = [];
    const authFn = authorize
        ? (t, c, ch) => { authCalls.push({ t, c, ch }); return authorize(t, c, ch); }
        : (t, c, ch) => { authCalls.push({ t, c, ch }); return true; };

    const { server, io } = createGateway({ authorize: authFn, redisPub, redisSub });
    return new Promise((resolve) => {
        server.listen(0, () => {
            resolve({
                port: server.address().port,
                server, io, redisPub, redisSub, authCalls,
                close: () => new Promise((r) => { io.close(); server.close(() => r()); }),
            });
        });
    });
}

function connect(port, auth) {
    return ioClient(`http://127.0.0.1:${port}`, {
        auth, transports: ['websocket'], forceNew: true, reconnection: false,
    });
}

// Await an event with a timeout so a missed event fails loudly instead of hanging.
function waitFor(emitter, event, ms = 2000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error(`timeout waiting for "${event}"`)), ms);
        emitter.once(event, (...args) => { clearTimeout(timer); resolve(args.length > 1 ? args : args[0]); });
    });
}

// Promisified emit-with-ack + timeout.
function emitAck(socket, event, payload, ms = 2000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error(`ack timeout for "${event}"`)), ms);
        socket.emit(event, payload, (res) => { clearTimeout(timer); resolve(res); });
    });
}

// ── parseLocation (pure) ───────────────────────────────────────────

test('parseLocation: happy path returns parsed region/driver/coords', () => {
    const r = parseLocation({ channel: 'qa.driver.7', lat: 25.28, lng: 51.53 }, () => true);
    assert.deepEqual(r, { ok: true, channel: 'qa.driver.7', region: 'qa', driverId: '7', lat: 25.28, lng: 51.53 });
});

test('parseLocation: lowercases the region', () => {
    assert.equal(parseLocation({ channel: 'QA.driver.7', lat: 1, lng: 2 }, () => true).region, 'qa');
});

test('parseLocation: rejection matrix', () => {
    const has = () => true;
    assert.equal(parseLocation(null, has).reason, 'bad_payload');
    assert.equal(parseLocation('nope', has).reason, 'bad_payload');
    assert.equal(parseLocation(42, has).reason, 'bad_payload');
    // channel not a string, or socket not joined to it
    assert.equal(parseLocation({ channel: 123, lat: 1, lng: 2 }, has).reason, 'not_subscribed');
    assert.equal(parseLocation({ channel: 'qa.driver.7', lat: 1, lng: 2 }, () => false).reason, 'not_subscribed');
    // non-finite coords
    assert.equal(parseLocation({ channel: 'qa.driver.7', lat: 'x', lng: 2 }, has).reason, 'bad_coords');
    assert.equal(parseLocation({ channel: 'qa.driver.7', lat: 1 }, has).reason, 'bad_coords');
    // wrong channel shape
    assert.equal(parseLocation({ channel: 'qa.rider.7', lat: 1, lng: 2 }, has).reason, 'bad_channel');
    assert.equal(parseLocation({ channel: 'qa.driver.abc', lat: 1, lng: 2 }, has).reason, 'bad_channel');
    assert.equal(parseLocation({ channel: 'qa.driver.7.extra', lat: 1, lng: 2 }, has).reason, 'bad_channel');
    // blank region — the silent-discard guard
    assert.equal(parseLocation({ channel: '.driver.7', lat: 1, lng: 2 }, has).reason, 'blank_region');
});

// ── Connection auth ────────────────────────────────────────────────

test('connection without a token is rejected and disconnected', async () => {
    const gw = await startGateway();
    const client = connect(gw.port, {}); // no token
    try {
        // Register the disconnect listener up front — the server disconnects
        // immediately after emitting, so waiting only afterwards can miss it.
        const disconnected = waitFor(client, 'disconnect');
        const reason = await waitFor(client, 'unauthorized');
        assert.deepEqual(reason, { reason: 'missing_token' });
        await disconnected;
    } finally {
        client.close();
        await gw.close();
    }
});

test('connection with a token stays connected', async () => {
    const gw = await startGateway();
    const client = connect(gw.port, { token: 't1' });
    try {
        await waitFor(client, 'connect');
        assert.equal(client.connected, true);
    } finally {
        client.close();
        await gw.close();
    }
});

// ── subscribe authorization ────────────────────────────────────────

test('subscribe to an authorized channel acks authorized:true and passes token/country', async () => {
    const gw = await startGateway({ authorize: (_t, _c, ch) => ch === 'qa.office.1' });
    const client = connect(gw.port, { token: 'tkn', country: 'QA' });
    try {
        await waitFor(client, 'connect');
        const res = await emitAck(client, 'subscribe', 'qa.office.1');
        assert.deepEqual(res, { authorized: true });
        assert.equal(gw.authCalls[0].t, 'tkn');
        assert.equal(gw.authCalls[0].c, 'QA');
        assert.equal(gw.authCalls[0].ch, 'qa.office.1');
    } finally {
        client.close();
        await gw.close();
    }
});

test('subscribe to a forbidden channel acks authorized:false and does not join', async () => {
    // Deny everything; a denied subscribe must not join, proven by the fact that
    // a later location publish to that channel is rejected not_subscribed.
    const gw = await startGateway({ authorize: () => false });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        const res = await emitAck(client, 'subscribe', 'qa.driver.7');
        assert.deepEqual(res, { authorized: false });
        const loc = await emitAck(client, 'driver.location', { channel: 'qa.driver.7', lat: 1, lng: 2 });
        assert.deepEqual(loc, { stored: false, reason: 'not_subscribed' });
    } finally {
        client.close();
        await gw.close();
    }
});

// ── driver.location end-to-end ─────────────────────────────────────

test('a subscribed driver location is stored, mirrored, and echoed to the room', async () => {
    const gw = await startGateway({ authorize: (_t, _c, ch) => ch === 'qa.driver.7' });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.driver.7');

        const framePromise = waitFor(client, 'qa.driver.7:driver.location');
        const ack = await emitAck(client, 'driver.location', { channel: 'qa.driver.7', lat: 25.2854, lng: 51.531 });

        assert.equal(ack.stored, true);
        assert.equal(typeof ack.ts, 'number');

        // Redis geo write: geoadd(key, lng, lat, driverId) — lng BEFORE lat.
        assert.deepEqual(gw.redisPub.calls.geoadd[0], ['fleet:geo:qa', 51.531, 25.2854, '7']);
        assert.equal(gw.redisPub.calls.set[0][0], 'fleet:loc:qa:7');
        // Mirrored onto the bus with socket:true so the relay won't double-deliver.
        const [chan, raw] = gw.redisPub.calls.publish[0];
        assert.equal(chan, 'rt:qa.driver.7');
        assert.equal(JSON.parse(raw).socket, true);

        // The room echo carries the frame.
        const frame = await framePromise;
        assert.equal(frame.lat, 25.2854);
        assert.equal(frame.lng, 51.531);
        assert.equal(frame.channel, 'qa.driver.7');
    } finally {
        client.close();
        await gw.close();
    }
});

test('location with non-finite coords is rejected bad_coords and nothing is stored', async () => {
    const gw = await startGateway({ authorize: () => true });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.driver.7');
        const ack = await emitAck(client, 'driver.location', { channel: 'qa.driver.7', lat: 'NaN', lng: 51 });
        assert.deepEqual(ack, { stored: false, reason: 'bad_coords' });
        assert.equal(gw.redisPub.calls.geoadd.length, 0);
    } finally {
        client.close();
        await gw.close();
    }
});

test('location to a blank-region channel is rejected, not silently discarded', async () => {
    const gw = await startGateway({ authorize: () => true }); // allow the join
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', '.driver.7');
        const ack = await emitAck(client, 'driver.location', { channel: '.driver.7', lat: 1, lng: 2 });
        assert.deepEqual(ack, { stored: false, reason: 'blank_region' });
        assert.equal(gw.redisPub.calls.geoadd.length, 0);
    } finally {
        client.close();
        await gw.close();
    }
});

test('a redis store failure acks store_failed (positive ack means persisted)', async () => {
    const gw = await startGateway({ authorize: () => true, pubOpts: { failExec: true } });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.driver.7');
        const ack = await emitAck(client, 'driver.location', { channel: 'qa.driver.7', lat: 25, lng: 51 });
        assert.deepEqual(ack, { stored: false, reason: 'store_failed' });
        // No mirror publish happened because persistence threw first.
        assert.equal(gw.redisPub.calls.publish.length, 0);
    } finally {
        client.close();
        await gw.close();
    }
});

// ── Redis → Socket.IO relay ────────────────────────────────────────

test('a redis pmessage is relayed as room:event to a joined client', async () => {
    const gw = await startGateway({ authorize: () => true });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.office.1');

        const dataPromise = waitFor(client, 'qa.office.1:booking.status_changed');
        gw.redisSub.emit(
            'pmessage', 'rt:*', 'rt:qa.office.1',
            JSON.stringify({ event: 'booking.status_changed', data: { bookingId: 56, status: 'on_trip' } }),
        );
        const data = await dataPromise;
        assert.deepEqual(data, { bookingId: 56, status: 'on_trip' });
    } finally {
        client.close();
        await gw.close();
    }
});

test('a socket:true mirror message is NOT relayed (no double delivery)', async () => {
    const gw = await startGateway({ authorize: () => true });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.driver.7');

        // Collect every frame that actually reaches the client. The mirror
        // (socket:true, lat:1) must never appear; the genuine bus frame
        // (socket:false, lat:2) must — proving the relay is alive and the skip
        // was specific to socket:true, not a dead relay.
        const receivedLats = [];
        client.on('qa.driver.7:driver.location', (d) => receivedLats.push(d.lat));
        const realPromise = waitFor(client, 'qa.driver.7:driver.location');
        gw.redisSub.emit('pmessage', 'rt:*', 'rt:qa.driver.7',
            JSON.stringify({ event: 'driver.location', data: { lat: 1 }, socket: true }));
        gw.redisSub.emit('pmessage', 'rt:*', 'rt:qa.driver.7',
            JSON.stringify({ event: 'driver.location', data: { lat: 2 }, socket: false }));
        await realPromise;
        // Let any (erroneous) extra delivery flush before asserting.
        await new Promise((r) => setTimeout(r, 50));
        assert.deepEqual(receivedLats, [2]);
    } finally {
        client.close();
        await gw.close();
    }
});

test('a malformed pmessage payload is ignored, not thrown', async () => {
    const gw = await startGateway({ authorize: () => true });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.office.1');
        // Bad JSON first (must be swallowed), then a good frame that must arrive.
        gw.redisSub.emit('pmessage', 'rt:*', 'rt:qa.office.1', '{not json');
        const dataPromise = waitFor(client, 'qa.office.1:ping');
        gw.redisSub.emit('pmessage', 'rt:*', 'rt:qa.office.1', JSON.stringify({ event: 'ping', data: { ok: 1 } }));
        assert.deepEqual(await dataPromise, { ok: 1 });
    } finally {
        client.close();
        await gw.close();
    }
});

// ── unsubscribe ────────────────────────────────────────────────────

test('after unsubscribe, a location to that channel is rejected not_subscribed', async () => {
    const gw = await startGateway({ authorize: () => true });
    const client = connect(gw.port, { token: 'tkn' });
    try {
        await waitFor(client, 'connect');
        await emitAck(client, 'subscribe', 'qa.driver.7');
        client.emit('unsubscribe', 'qa.driver.7');
        // Give the leave a tick to apply.
        await new Promise((r) => setTimeout(r, 100));
        const ack = await emitAck(client, 'driver.location', { channel: 'qa.driver.7', lat: 1, lng: 2 });
        assert.deepEqual(ack, { stored: false, reason: 'not_subscribed' });
    } finally {
        client.close();
        await gw.close();
    }
});
