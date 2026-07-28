/*
 * watch-location.js — live monitor for a driver's realtime location channel.
 *
 * Usage:
 *   node watch-location.js <phone> [country]
 *   node watch-location.js +974933817392 QA
 *
 * It logs in with the dev OTP flow (the backend echoes the code locally),
 * discovers the driver id, subscribes to that driver's private channel
 * (driver.{id}), and prints every `driver.location` frame the moment it fires.
 * Other events on the channel are printed dimmed.
 */
const { io } = require('socket.io-client');
const axios = require('axios');

const PHONE = process.argv[2] || '+974933817392';
const COUNTRY = process.argv[3] || 'QA';
const API = (process.env.APP_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const RT = 'http://127.0.0.1:' + (process.env.FLEET_RT_PORT || '6002');

const http = axios.create({
  baseURL: API,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Country': COUNTRY },
  validateStatus: () => true,
});

const ts = () => new Date().toISOString().slice(11, 19);

async function login() {
  const req = await http.post('/driver/auth/otp/request', { phone: PHONE, country: COUNTRY });
  const code = req.data && req.data.data && req.data.data.dev_code;
  if (!code) {
    throw new Error('no dev_code (throttled? wait 60s) — response: ' + JSON.stringify(req.data && req.data.data));
  }
  const ver = await http.post('/driver/auth/otp/verify', { phone: PHONE, code, country: COUNTRY });
  const d = ver.data && ver.data.data;
  if (!d || !d.access_token || !d.driver) {
    throw new Error('verify failed: ' + JSON.stringify(d));
  }
  return { token: d.access_token, driverId: d.driver.id };
}

(async () => {
  console.log(`[watch] logging in ${PHONE} (${COUNTRY}) …`);
  const { token, driverId } = await login();
  // Region-namespaced channel (e.g. `sy.driver.33`) — must match the shard the
  // driver logged in through.
  const channel = COUNTRY.toLowerCase() + '.driver.' + driverId;
  console.log(`[watch] driver id=${driverId} → channel "${channel}"`);

  const socket = io(RT, { transports: ['websocket'], auth: { token, country: COUNTRY } });

  socket.on('connect', () => {
    console.log(`[watch] connected ${socket.id} — subscribing to ${channel}`);
    socket.emit('subscribe', channel, (ack) => {
      if (ack && ack.authorized) {
        console.log(`[watch] subscribed ✓  — waiting for events (Ctrl+C to stop)\n`);
      } else {
        console.log('[watch] NOT authorized for ' + channel + ' — is this the right driver?');
        process.exit(1);
      }
    });
  });

  let n = 0;
  socket.onAny((event, data) => {
    const type = event.slice(event.indexOf(':') + 1);
    if (type === 'driver.location') {
      n++;
      console.log(`${ts()}  📍 LOCATION #${n}  lat=${data.lat}  lng=${data.lng}  (ts=${data.ts})`);
    } else if (event.includes(':')) {
      console.log(`${ts()}  · ${type}  ${JSON.stringify(data)}`);
    }
  });

  socket.on('disconnect', (r) => console.log(`${ts()}  [watch] disconnected (${r})`));
  socket.on('connect_error', (e) => console.log(`${ts()}  [watch] connect_error ${e.message}`));
})().catch((e) => {
  console.error('[watch] error:', e.message);
  process.exit(1);
});
