/*
 * watch-raw.js — RAW firehose. Prints EVERY realtime message on the bus with no
 * filtering, no parsing, no formatting: just `time  channel  payload`. Includes
 * every domain event AND the mirrored driver.location, for every country/driver.
 *
 * Usage:  node watch-raw.js
 */
const Redis = require('ioredis');

const PREFIX = process.env.FLEET_RT_PREFIX || 'rt:';
const sub = new Redis({
  host: process.env.REDIS_HOST || '127.0.0.1',
  port: parseInt(process.env.REDIS_PORT || '6380', 10),
  password: process.env.REDIS_PASSWORD || null,
});

sub.psubscribe(PREFIX + '*', (err, count) => {
  if (err) { console.error('psubscribe failed:', err.message); process.exit(1); }
  console.log(`[watch-raw] ${PREFIX}* — printing every message (Ctrl+C to stop)\n`);
});

sub.on('pmessage', (_pattern, channel, raw) => {
  console.log(new Date().toISOString().slice(11, 19), channel, raw);
});
