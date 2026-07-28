/**
 * Scenario harness — stands in for the driver app.
 *
 * Connects to the gateway exactly like `LiveSocketService` does, subscribes to
 * the driver's region-namespaced room, pushes one location fix (ACK-gated, so a
 * positive ack means Redis really has the position), then prints EVERY frame
 * that arrives. Used to verify that a rider's booking actually reaches the
 * driver through dispatch → outbox → relay → gateway → socket.
 *
 *   node scenario-driver.js <driverId> <token> [region] [lat] [lng]
 */
const { io } = require('socket.io-client');

const [driverId, token, region = 'qa', lat = '25.2854', lng = '51.5310'] = process.argv.slice(2);

if (!driverId || !token) {
    console.error('usage: node scenario-driver.js <driverId> <token> [region] [lat] [lng]');
    process.exit(1);
}

const URL = process.env.GATEWAY_URL || 'http://127.0.0.1:6002';
const room = `${region}.driver.${driverId}`;
const country = region.toUpperCase();

const ts = () => new Date().toISOString().slice(11, 23);
const log = (...a) => console.log(`[${ts()}]`, ...a);

log(`connecting ${URL} as driver ${driverId} (country=${country}) → room ${room}`);

const socket = io(URL, {
    transports: ['websocket'],
    auth: { token, country },
});

socket.on('connect', () => {
    log('CONNECTED', socket.id);

    // `subscribe` takes the room as a bare STRING (what LiveSocketService._subscribe
    // sends); `driver.location` takes an object. Mismatching these silently
    // yields authorized:false.
    socket.emit('subscribe', room, (res) => {
        log('SUBSCRIBE ack:', JSON.stringify(res));

        // Push the position the dispatch radius search will match on.
        socket.emit(
            'driver.location',
            { channel: room, lat: Number(lat), lng: Number(lng) },
            (ack) => {
                log('LOCATION ack:', JSON.stringify(ack));
                if (!ack || ack.stored !== true) {
                    log('!! position NOT stored — dispatch cannot match this driver');
                }
                log('listening for offers… (Ctrl-C to stop)');
            },
        );
    });
});

// Print everything, unfiltered — the gateway frames events as "{room}:{event}".
socket.onAny((event, ...args) => {
    if (event === 'connect' || event === 'disconnect') return;
    log('EVENT', event, JSON.stringify(args).slice(0, 900));
});

socket.on('connect_error', (e) => log('CONNECT_ERROR', e.message));
socket.on('disconnect', (r) => log('DISCONNECTED', r));
