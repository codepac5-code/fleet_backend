// FleetOS realtime gateway — production entrypoint. Parses env, wires the REAL
// Redis clients + PHP `/realtime/authorize` check into createGateway(), and
// starts listening. All handler logic lives in gateway.js (unit/integration
// tested); this file only supplies dependencies. `node server.js` behaves
// exactly as before.

const Redis = require('ioredis');
const axios = require('axios');

require('dotenv').config();

const { createGateway } = require('./gateway');

const PORT = parseInt(process.env.FLEET_RT_PORT || '6002', 10);
const CHANNEL_PREFIX = process.env.FLEET_RT_PREFIX || 'rt:';
// Terminal event monitor: run with FLEET_RT_LOG=1 to print every socket event.
const LOG = process.env.FLEET_RT_LOG === '1' || process.env.FLEET_RT_LOG === 'true';
// How long a stored driver position stays valid (seconds). Must match PHP
// DriverLocationStore::DEFAULT_TTL.
const LOCATION_TTL = parseInt(process.env.FLEET_RT_LOCATION_TTL || '3600', 10);
const log = (...a) => { if (LOG) console.log(new Date().toISOString(), '[rt]', ...a); };
const AUTHORIZE_URL = (process.env.APP_URL || 'http://127.0.0.1:8000').replace(/\/$/, '') + '/realtime/authorize';
const CORS_ORIGIN = (process.env.FLEET_RT_CORS || '*') === '*'
    ? '*'
    : (process.env.FLEET_RT_CORS || '').split(',').map((o) => o.trim()).filter(Boolean);

const redisOpts = {
    host: process.env.REDIS_HOST || '127.0.0.1',
    port: parseInt(process.env.REDIS_PORT || '6379', 10),
    password: process.env.REDIS_PASSWORD || null,
};
const sub = new Redis(redisOpts);
// Separate connection for publishing (a subscriber connection cannot publish).
const pub = new Redis(redisOpts);

// Ask the PHP app whether this token may join `channel` (the real auth boundary;
// see the note in routes/driver.php — `auth:driver` alone is not identity).
async function httpAuthorize(token, country, channel) {
    if (!token || typeof channel !== 'string' || channel === '') {
        return false;
    }

    const headers = { Authorization: 'Bearer ' + token, Accept: 'application/json' };
    if (country) {
        headers['X-Country'] = country;
    }

    try {
        const res = await axios.post(AUTHORIZE_URL, { channel }, {
            headers,
            validateStatus: () => true,
            timeout: 5000,
        });

        return res.status === 200 && res.data && res.data.authorized === true;
    } catch (e) {
        console.error('[fleet-rt] authorize error:', e.message);
        return false;
    }
}

const { server } = createGateway({
    authorize: httpAuthorize,
    redisPub: pub,
    redisSub: sub,
    log,
    locationTtl: LOCATION_TTL,
    channelPrefix: CHANNEL_PREFIX,
    corsOrigin: CORS_ORIGIN,
});

server.listen(PORT, () => {
    console.log(`[fleet-rt] gateway listening on port ${PORT}`);
});
