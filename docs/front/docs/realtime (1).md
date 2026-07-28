# FleetOS Realtime — Frontend Integration Guide

How the **Fleet Ride** (rider) and **DriverX** (driver) apps connect to the FleetOS realtime
gateway, subscribe to their channels, and consume live events. This contract is enforced by the
backend (`realtime-gateway/server.js` + `POST /realtime/authorize` + `ChannelAuthorizer`).

---

## 1. Architecture (what to picture)

```
Backend action (REST)  ──►  Transactional outbox  ──►  Redis  rt:{channel}  (pub/sub)
                                                          │
                                             realtime-gateway (Socket.IO)
                                                          │  emits  "{channel}:{event}"
                                                          ▼
                                              your app (subscribed to its rooms)
```

- **You never send domain events over the socket.** All *actions* (book a ride, accept an offer,
  send a chat message, rate a trip …) go through the **REST API**. The socket is **receive‑only**
  for domain events, plus two control messages: `subscribe` / `unsubscribe`.
- The socket is a **live notifier**, not the source of truth. Treat REST as authoritative; use the
  socket to know *when* to update the UI or re‑fetch.

---

## 2. Connect

- **URL:** `wss://<host>:<FLEET_RT_PORT>` (default port `6002`). Use `ws://` only in local dev.
- **Client:** [Socket.IO client](https://socket.io/) `^4` (matches the server’s `socket.io ^4`).
- **Auth handshake:** pass your **Passport access token** (the same `access_token` returned by
  `POST /api/v1/auth/otp/verify`) and the **country code** (same `X-Country` you send to the REST API).

```js
import { io } from 'socket.io-client';

const socket = io('wss://rt.fleetos.app:6002', {
  transports: ['websocket'],
  auth: {
    token: accessToken,   // Passport bearer token from login
    country: 'SY',        // ISO‑2, same as the X-Country REST header
  },
  reconnection: true,           // default; keep it on
  reconnectionDelay: 1000,
  reconnectionDelayMax: 5000,
});
```

If `token` is missing the server emits `unauthorized` and disconnects immediately:

```js
socket.on('unauthorized', (e) => { /* re-login / refresh token */ });
```

> The `country` is required so the gateway can resolve the correct country shard when authorizing
> **booking** channels (driver identity and ride ownership live in the per‑country database).

---

## 3. Subscribe to channels

Subscribing is an **acknowledged** request. The server calls `POST /realtime/authorize` with your
token; you only join the room if you are authorized. Always check the ack.

```js
function subscribe(channel) {
  return new Promise((resolve) => {
    socket.emit('subscribe', channel, (res) => resolve(res && res.authorized === true));
  });
}

// unsubscribe (e.g. when a ride ends)
socket.emit('unsubscribe', channel);
```

### Which channels each app subscribes to

| Channel | Who | When |
|---|---|---|
| `user.{myUserId}` | Rider app | For the whole session (account‑level events) |
| `driver.{myDriverId}` | Driver app | For the whole session (offers, dues, presence) |
| `booking.{bookingId}` | Both | Only for the **active** ride; unsubscribe when it ends |

**Authorization is enforced server‑side** — you can only join *your own* `user.{id}` / `driver.{id}`,
and a `booking.{id}` **only if you are a party to that ride** (its rider or its assigned driver).
Anything else (e.g. `office.{id}`, someone else’s id) is denied. Do not rely on client checks; the
server is the gate.

---

## 4. Receiving events — the naming rule

The gateway emits each event as **`"{channel}:{eventType}"`** with the event payload as the single
argument. So you listen per channel + event:

```js
socket.on('user.5:booking.status_changed', (data) => { /* … */ });
socket.on('booking.4100:driver.location',  (data) => { /* … */ });
```

Because the id is baked into the event name, build the listener name from the id you subscribed to:

```js
function on(channel, eventType, handler) {
  socket.on(`${channel}:${eventType}`, handler);
}
on(`user.${userId}`, 'booking.status_changed', updateActiveRide);
on(`booking.${bookingId}`, 'driver.location', moveDriverPin);
```

---

## 5. Event catalog

`eventType` values are stable strings. Payloads are JSON objects; **treat every field as optional**
and re‑fetch via REST for authoritative state. Grouped by the app that cares most.

### Rider app — subscribe `user.{id}` and `booking.{activeId}`

| eventType | Channel(s) | Meaning → do |
|---|---|---|
| `booking.status_changed` | `user.{id}`, `booking.{id}` | Ride moved (matching→assigned→arriving→arrived→on_trip→completed/cancelled). Update the ride screen; on terminal states unsubscribe the booking channel. Payload: `{ booking_id, status, office_id, source }` |
| `dispatch.ride_assigned` | `booking.{id}`, `user.{id}` | A driver accepted. Show the assigned driver. Payload: `{ booking_id, driver_id, office_id }` |
| `driver.location` | `booking.{id}` | Live driver position while en route/on trip. Move the map pin (throttle UI). Payload: `{ driver_id, lat, lng }` |
| `booking.chat_message` | `booking.{id}` | New in‑trip message. Append to chat / re‑fetch thread. |
| `ride.released` | `booking.{id}` | Fare settled (trip fully closed). Show the receipt (fetch `GET /bookings/{id}/receipt`). |
| `chat.message_created` | `user.{id}` | New message in a rider↔office conversation. |
| `support.message_created` | `user.{id}` | New reply on a support ticket. |
| `wallet.credited` | `user.{id}` | Wallet top‑up/refund landed. Refresh balance. |
| `payment.succeeded` | `user.{id}` | A payment intent succeeded. |
| `rating.received` | `user.{id}` | You were rated (dual rating). |

### Driver app — subscribe `driver.{id}` and `booking.{activeId}`

| eventType | Channel(s) | Meaning → do |
|---|---|---|
| `dispatch.offer_created` | `driver.{id}` | A ride offer for you, with a TTL. Show the offer sheet + countdown. Payload: `{ booking_id, office_id, distance_m, expires_at }`. Accept via `POST /driver/offers/{booking}/accept`. |
| `dispatch.offer_expired` | `driver.{id}`, `booking.{id}` | The offer timed out or was taken. Dismiss the offer sheet. |
| `dispatch.ride_assigned` | `booking.{id}`, `driver.{id}` | You won the ride (or another driver did — check `driver_id`). |
| `booking.status_changed` | `booking.{id}` | Ride state changed. Keep the trip screen in sync. |
| `presence.changed` | `driver.{id}` | Your online/busy state changed (e.g. auto‑busy on assignment). |
| `booking.chat_message` | `booking.{id}` | New in‑trip message from the rider. |
| `wallet.payout` | `driver.{id}` | A payout request was processed. |
| `wallet.credited` | `driver.{id}` | Your earnings/wallet changed. |
| `ride.released` | `booking.{id}` | Trip settled; your fare share credited. Refresh earnings. |
| `rating.received` | `driver.{id}` | The rider rated you. |

> Office/admin dashboards receive `office.{id}` events (dispatch, presence, subscription.*,
> chat/support). Those panels authenticate by web session, not by this token flow, and are out of
> scope for the mobile apps.

---

## 6. Reconnection & missed events (important)

Redis pub/sub is **fire‑and‑forget**: events emitted while you were disconnected are **not
replayed**. Design for this:

1. **Re‑subscribe on every (re)connect.** Socket.IO auto‑reconnects but the server does not remember
   your rooms — you must re‑send `subscribe` after `connect`.
2. **Snapshot‑on‑connect.** Right after (re)connecting, fetch authoritative state via REST
   (`GET /bookings/{id}`, `GET /driver/home`, wallet balance, …) and reconcile the UI. Then let live
   events keep it fresh.

```js
async function resubscribeAll() {
  await subscribe(`user.${userId}`);            // or driver.{id}
  if (activeBookingId) await subscribe(`booking.${activeBookingId}`);
  await refreshFromRest();                        // snapshot to catch anything missed
}

socket.on('connect', resubscribeAll);
socket.on('disconnect', (reason) => { /* show "reconnecting…" if reason !== 'io client disconnect' */ });
socket.on('connect_error', (err) => { /* backoff handled by Socket.IO; surface only if persistent */ });
```

---

## 7. Sending events — you don’t

There is **no** client→server domain messaging. To *cause* something, call REST; the resulting event
is pushed back to the relevant channel. Examples:

| You want to… | Call (REST) | You’ll then receive |
|---|---|---|
| Book a ride | `POST /api/v1/bookings` | `dispatch.offer_created` (drivers), `booking.status_changed` |
| Accept an offer (driver) | `POST /api/v1/driver/offers/{booking}/accept` | `dispatch.ride_assigned` |
| Update driver location | `POST /api/v1/driver/trips/{booking}/location` | `driver.location` (to the rider) |
| Send in‑trip message | `POST /api/v1/bookings/{booking}/chat` | `booking.chat_message` |

The only socket‑level messages you send are `subscribe` and `unsubscribe`.

---

## 8. Security notes

- The socket carries **no data you aren’t authorized for**: every `subscribe` is verified against your
  token by the backend, per channel, on every request. A stolen id in a channel name gets you nothing.
- Use **`wss://`** (TLS) in production. Terminate TLS at your reverse proxy in front of port `6002`.
- Set **`FLEET_RT_CORS`** to your app origins in production (comma‑separated) instead of `*`.
- Rotate the token via your normal login/refresh flow; on `unauthorized` or repeated `connect_error`
  after a token change, reconnect with the fresh token in `auth.token`.

---

## 9. Minimal end‑to‑end example (rider)

```js
import { io } from 'socket.io-client';

export function connectRealtime({ url, token, country, userId, getActiveBookingId, onEvent }) {
  const socket = io(url, { transports: ['websocket'], auth: { token, country } });

  const sub = (ch) => new Promise((r) => socket.emit('subscribe', ch, (a) => r(!!(a && a.authorized))));
  const bind = (ch, ev, fn) => socket.on(`${ch}:${ev}`, fn);

  async function wire() {
    await sub(`user.${userId}`);
    const b = getActiveBookingId();
    if (b) await sub(`booking.${b}`);

    bind(`user.${userId}`, 'booking.status_changed', (d) => onEvent('status', d));
    bind(`user.${userId}`, 'wallet.credited',        (d) => onEvent('wallet', d));
    const b2 = getActiveBookingId();
    if (b2) {
      bind(`booking.${b2}`, 'driver.location', (d) => onEvent('location', d));
      bind(`booking.${b2}`, 'ride.released',   (d) => onEvent('receipt', d));
    }
  }

  socket.on('connect', wire);
  socket.on('unauthorized', () => onEvent('unauthorized'));
  return socket;
}
```

---

## 9b. Minimal end‑to‑end example (driver)

Covers the driver flow: go online (REST) → receive **offers** with a live countdown → **accept**
(REST) → subscribe the ride’s `booking` channel → drive the trip while pushing location (REST) →
settle. The socket only *notifies*; every action is a REST call.

```js
import { io } from 'socket.io-client';

export function connectDriverRealtime({ url, token, country, driverId, api, ui }) {
  const socket = io(url, { transports: ['websocket'], auth: { token, country } });

  const sub   = (ch)         => new Promise((r) => socket.emit('subscribe', ch, (a) => r(!!(a && a.authorized))));
  const unsub = (ch)         => socket.emit('unsubscribe', ch);
  const bind  = (ch, ev, fn) => socket.on(`${ch}:${ev}`, fn);

  const me = `driver.${driverId}`;
  let activeBooking = null;
  let offerTimer = null;

  function clearOffer() { if (offerTimer) { clearInterval(offerTimer); offerTimer = null; } ui.hideOffer(); }

  async function wire() {
    // 1) always (re)subscribe your own channel
    await sub(me);

    // 2) if you were mid‑trip, re‑subscribe its channel and reconcile via REST (missed‑event safety)
    const home = await api.get('/driver/home');           // snapshot on (re)connect
    activeBooking = home.active_booking_id || null;
    if (activeBooking) await sub(`booking.${activeBooking}`);
    ui.render(home);

    // 3) OFFERS — shown with a countdown derived from expires_at
    bind(me, 'dispatch.offer_created', (d) => {
      // d = { booking_id, office_id, distance_m, expires_at }
      ui.showOffer(d);
      clearInterval(offerTimer);
      offerTimer = setInterval(() => {
        const left = Math.max(0, Math.floor((Date.parse(d.expires_at) - Date.now()) / 1000));
        ui.offerCountdown(left);
        if (left <= 0) clearOffer();
      }, 250);

      ui.onAcceptOffer(async () => {
        clearOffer();
        try {
          await api.post(`/driver/offers/${d.booking_id}/accept`);   // REST action
          // success is confirmed by the dispatch.ride_assigned event below
        } catch (e) {
          if (e.status === 409) ui.toast('Offer already taken');     // atomic claim lost
        }
      });
      ui.onRejectOffer(() => { clearOffer(); api.post(`/driver/offers/${d.booking_id}/reject`); });
    });

    bind(me, 'dispatch.offer_expired', (d) => { if (activeBooking !== d.booking_id) clearOffer(); });

    // 4) You won the ride → start the trip, subscribe its channel
    bind(me, 'dispatch.ride_assigned', async (d) => {
      if (d.driver_id !== driverId) return;               // another driver won
      clearOffer();
      activeBooking = d.booking_id;
      await sub(`booking.${activeBooking}`);
      const trip = await api.get(`/driver/trips/${activeBooking}`);
      ui.openTrip(trip);
    });

    // 5) Presence / earnings
    bind(me, 'presence.changed', (d) => ui.setPresence(d.status));   // e.g. auto‑busy on assignment
    bind(me, 'wallet.payout',    (d) => ui.refreshEarnings());
    bind(me, 'wallet.credited',  (d) => ui.refreshEarnings());
    bind(me, 'rating.received',  (d) => ui.showRating(d));

    // 6) Trip channel: bind only when a booking is active
    if (activeBooking) bindTrip(activeBooking);
  }

  function bindTrip(bookingId) {
    const ch = `booking.${bookingId}`;
    bind(ch, 'booking.status_changed', (d) => ui.updateTrip(d));     // arriving→arrived→on_trip…
    bind(ch, 'booking.chat_message',   (d) => ui.appendChat(d));
    bind(ch, 'ride.released', async () => {                          // settled → close + refresh
      ui.closeTrip();
      await api.get('/driver/earnings');
      unsub(ch);
      activeBooking = null;
    });
  }

  socket.on('connect', wire);
  socket.on('unauthorized', () => ui.forceRelogin());
  socket.on('disconnect', (r) => { if (r !== 'io client disconnect') ui.reconnecting(); });

  return socket;
}
```

### Driver trip actions are REST (the socket just echoes the result)

| Step | REST call | Event you then receive |
|---|---|---|
| Go online / heartbeat | `POST /driver/presence` | `presence.changed` (self) |
| Accept / reject offer | `POST /driver/offers/{booking}/accept` \| `/reject` | `dispatch.ride_assigned` |
| Navigate to pickup | `POST /driver/trips/{booking}/navigate-pickup` | `booking.status_changed` (arriving) |
| Arrived | `POST /driver/trips/{booking}/arrived` | `booking.status_changed` (arrived) |
| Start trip | `POST /driver/trips/{booking}/start` | `booking.status_changed` (on_trip) |
| Push GPS (throttle ~5s) | `POST /driver/trips/{booking}/location` | `driver.location` (to the rider) |
| End trip | `POST /driver/trips/{booking}/end` | `booking.status_changed` (completed) → `ride.released` |

> **Offer acceptance is atomic on the server.** Two drivers can be offered the same ride; the first
> `accept` wins and the loser gets `409` (handle it as “already taken”). Never assume acceptance from
> the tap — wait for `dispatch.ride_assigned` with your `driver_id`.

---

## 10. Server config reference (ops)

| Env | Default | Purpose |
|---|---|---|
| `FLEET_RT_PORT` | `6002` | Gateway listen port |
| `FLEET_RT_PREFIX` | `rt:` | Redis channel prefix it psubscribes |
| `FLEET_RT_CORS` | `*` | Allowed Socket.IO origins (set explicitly in prod) |
| `APP_URL` | `http://127.0.0.1:8000` | Backend base for `POST /realtime/authorize` |
| `REDIS_HOST` / `REDIS_PORT` / `REDIS_PASSWORD` | localhost:6379 | Redis pub/sub |

Run: `cd realtime-gateway && npm install && npm start` (or via Supervisor — see `deploy/supervisor/fleetos.conf`).
Health check: `GET http://<host>:6002/health` → `{ "ok": true }`.

The backend must be relaying the outbox to Redis: keep `php artisan fleet:events-relay --daemon` running.
