/*
 * watch-events.js — live monitor for ALL realtime domain events across every
 * channel/country. Subscribes to the Redis pub/sub bus and prints each event
 * with its region + target, e.g.:
 *
 *   12:03:41  [sy]  driver.33      presence.changed   {"status":"online"}
 *   12:03:42  [qa]  booking.500    booking.status_changed {"status":"arrived"}
 *
 * Usage:  node watch-events.js  [channelFilter]
 *   node watch-events.js              # everything
 *   node watch-events.js sy           # only region "sy"
 *   node watch-events.js driver       # only driver.* channels
 *
 * Captures EVERY event without exception and without specifying a driver:
 * all backend domain events (presence/booking/offers/wallet/…) AND the live
 * driver.location (the gateway mirrors it onto the bus for observers).
 */
const Redis = require('ioredis');

const PREFIX = process.env.FLEET_RT_PREFIX || 'rt:';
const FILTER = (process.argv[2] || '').toLowerCase();
const sub = new Redis({
  host: process.env.REDIS_HOST || '127.0.0.1',
  port: parseInt(process.env.REDIS_PORT || '6380', 10),
  password: process.env.REDIS_PASSWORD || null,
});

const ts = () => new Date().toISOString().slice(11, 19);

sub.psubscribe(PREFIX + '*', (err, count) => {
  if (err) { console.error('psubscribe failed:', err.message); process.exit(1); }
  console.log(`[watch-events] subscribed to ${count} pattern(s): ${PREFIX}*` +
    (FILTER ? `  (filter="${FILTER}")` : '') + '\n');
});

let n = 0;
sub.on('pmessage', (_pattern, channel, raw) => {
  let m;
  try { m = JSON.parse(raw); } catch { return; }
  if (!m) return; // show everything, including the gateway's location mirror

  const room = channel.slice(PREFIX.length);           // e.g. sy.driver.33
  if (FILTER && !room.toLowerCase().includes(FILTER) && !String(m.event).toLowerCase().includes(FILTER)) return;

  const parts = room.split('.');
  let region = '--', entity = room;
  if (parts.length === 3) { region = parts[0]; entity = parts[1] + '.' + parts[2]; }
  else if (parts.length === 2) { entity = parts[0] + '.' + parts[1]; }

  n++;
  console.log(
    `${ts()}  [${region.padEnd(3)}] ${entity.padEnd(15)} ${String(m.event).padEnd(24)} ${JSON.stringify(m.data || {})}`
  );
});

process.on('SIGINT', () => { console.log(`\n[watch-events] ${n} event(s) seen. bye.`); sub.disconnect(); process.exit(0); });
