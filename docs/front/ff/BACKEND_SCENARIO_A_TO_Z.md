# Fleet Ride — Backend Scenario & Lifecycle (A → Z)

**For the backend developer.** This is the end-to-end story of the rider app: how the app boots,
what it calls, how the server turns each write into a realtime event, and how the whole trip
lifecycle flows from "open the app" to "trip settled + rated". It's the glue between the three
reference files — read those for exact shapes, read this for **how it all fits together**:

| Companion file | What it holds |
|---|---|
| `openapi.v2.yaml` / `api_examples.json` / `fleet_ride.postman_collection.json` | The REST contract — every endpoint, field, example. |
| `realtime (1).md` | The Socket.IO gateway contract (authoritative for the wire). |
| `RIDER_REALTIME_EVENTS.md` | Every realtime event the rider consumes + open asks. |

> **The one golden rule.** The client **never** sends domain events over the socket. Every action is
> a **REST write**; the write appends to a transactional **outbox**; a relay publishes the outbox row
> to **Redis**; the **gateway** fans it out to the subscribed rooms as `"{channel}:{eventType}"`. The
> socket is a *notifier*, never the source of truth. So for every scenario below the pattern is:
> **user action → REST → DB write → outbox → event(s) → rooms → app reacts (often re-fetching REST).**

---

## Part 0 — Foundations (apply to every request)

- **Base URL:** `/api/v1` · **Realtime:** `wss://<host>:<FLEET_RT_PORT>` (default `6002`).
- **Auth:** Laravel Passport bearer. `Authorization: Bearer <access_token>` on all but `/auth/*`.
- **Country shard:** send `X-Country: <ISO-2>` on REST and `country` in the socket handshake — driver
  identity + ride ownership live in a **per-country database**, so the gateway needs it to authorize
  `booking.*` joins.
- **Envelope (REST only):** every response is
  `{ status, statusCode, message, locale, data, error, meta }`. Socket frames are **raw JSON**, not
  enveloped.
- **Money:** integer **minor units** (`*_minor`) + a sibling `currency_code` (e.g. `QAR`, 2 decimals).
  Never floats.
- **Idempotency:** all create/pay POSTs accept an `Idempotency-Key` header (and `/bookings` also a
  body `idempotency_key`) — retries must be safe.
- **Localization:** `Accept-Language: en | ar`; the resolved language persists per user and is echoed
  as `locale`. Bilingual `*_ar`/`*_en` columns collapse server-side to one string.
- **Errors:** same envelope with `status:false`, a machine `error.code`, and a localized `message`.
  `401` → token refresh/relogin; `409` → idempotent conflict / lost race (e.g. offer taken).

---

## Part 1 — The event engine (how events are *triggered*)

```
REST write (controller)
   └─► same DB txn: INSERT event_outbox { channel, event, payload, country }
          └─► relay (php artisan fleet:events-relay --daemon)
                 └─► Redis PUBLISH rt:{channel}
                        └─► realtime-gateway (Socket.IO)  emits "{channel}:{event}"
                               └─► every client subscribed to {channel}
```

**Rooms (channels):** `user.{userId}` (a rider's whole session), `driver.{driverId}` (a driver's
session), `booking.{bookingId}` (one active ride; both parties join it). Authorization is enforced on
every `subscribe` via `POST /realtime/authorize` — a rider may join only its own `user.{id}` and a
`booking.{id}` it is a party to.

**Event naming is dotted** (`booking.status_changed`, `dispatch.ride_assigned`, `driver.location`).
Emit the **same** `booking.*` events to **both** the rider's and driver's `booking.{id}` room — that
shared room is what keeps the two apps in sync.

**Fire-and-forget:** Redis does not replay. If a client was offline it misses the event → the app
**re-subscribes on reconnect and snapshots via REST** (Part 3, S13). Design every event so the
authoritative state is always re-fetchable.

---

## Part 2 — Lifecycle timeline (one glance)

```
COLD START ─► bootstrap session ─► connect socket {token,country} ─► subscribe user.{id}
     │                                                                    │
     ▼                                                                    ▼
 no token ─► Auth (OTP/social) ─► token                         snapshot via REST (home, active ride?)
     │                                                                    │
     └────────────────────────────► HOME (idle) ◄─────────────────────────┘
                                        │
                request ride  ──────────┼──────────  scheduled ride
                                        ▼
                       ACTIVE RIDE  (subscribe booking.{id})
        matching ─► assigned ─► arriving ─► arrived ─► on_trip ─► completed
                                        │                             │
                                        ▼                             ▼
                               cancel / no_show               ride.released ─► receipt ─► rating
                                        │                             │
                                        └───────────► unsubscribe booking.{id} ◄──────────┘
                                                          back to HOME (idle)

BACKGROUND: no active ride → drop socket (push covers it) · active ride → keep socket alive
FOREGROUND: reconnect ─► re-subscribe ─► snapshot REST
```

---

## Part 3 — Scenario walkthroughs (A → Z)

Each step lists **trigger → REST → server writes → event(s) on room → app reaction**.

### S0 · Boot & session restore
1. App reads a stored token. If none → **S1 Auth**.
2. `GET /me` (`Authorization: Bearer …`) → confirms the session, returns the user + `locale`.
3. Socket connects: `auth:{ token, country }` → on `connect` the app **subscribes `user.{id}`**.
4. App snapshots: `GET /trips?status=active` (or the home payload) to learn if a ride is already live;
   if so it **subscribes `booking.{id}`** and restores the trip screen.
- **Server:** authorize the socket handshake against the token; authorize each `subscribe`.

### S1 · Authentication
| Trigger | REST | Result |
|---|---|---|
| Enter phone | `POST /auth/otp/request` | sends OTP, returns `challengeId` |
| Enter code | `POST /auth/otp/verify` | issues Passport **access + refresh** token pair |
| Google/Apple | `POST /auth/social` | verify provider token → issue token pair (new or existing user) |
| First-time details | `POST /auth/register` | completes the profile after OTP |
| Token expiry | `POST /auth/refresh` | new pair; socket reconnects with fresh `token` |
| Sign out | `POST /auth/logout` | revoke token; app disconnects socket |
| Delete account | `DELETE /account` | soft-delete (`users.deleted_at`) |
- No socket events here — auth is pure REST. After a token change the socket **reconnects** with the
  new token (on `unauthorized`, force relogin).

### S2 · Home & quote (idle)
1. `GET /catalog/services` + `GET /catalog/classes` → the **admin-driven** service/class catalog
   (never hardcode classes).
2. `GET /offices/search?lat&lng` → nearby offices; `GET /offices/{id}` for a profile;
   `POST /me/favorites/{officeId}` to pin.
3. `POST /places/suggest` (autocomplete) + `POST /geocode/reverse` (pin → address) for pickup/dropoff.
4. `POST /routes/estimate` → distance, duration, and per-class fares (minor units) for the quote.
- All read-only; no events. This is where the rider assembles a booking draft (service, class,
  office, pickup/dropoff, pickup note).

### S3 · Request a ride — the dispatch lifecycle (the core)
**Trigger:** rider taps *Request*.
1. **REST:** `POST /bookings` with `{ service, service_class, pricing_style, office_id, pickup_*,
   dropoff_*, pickup_note, scheduled_at:null, idempotency_key }`.
2. **Server writes:** insert `ride_bookings` (`status=matching`, `source=rider`), persist
   `pickup_note`, start dispatch (create timed `dispatch_offers` for candidate drivers).
3. **Events:**
   - `booking.status_changed` (`matching`) → **`user.{id}` + `booking.{id}`**
   - `dispatch.offer_created` `{ booking_id, office_id, distance_m, expires_at }` → **each candidate
     `driver.{id}`** (drivers show an offer sheet with a countdown).
4. **App reaction:** the rider immediately **subscribes `booking.{id}`** (using the returned id) and
   shows the *matching* screen.

**Driver accepts** (driver app, REST): `POST /driver/offers/{booking}/accept`.
- **Server:** atomic claim — first accept wins, losers get **`409`**. Set
  `ride_bookings.driver_id/vehicle_id`, `status=assigned`, driver presence `on_trip`.
- **Events:**
  - `dispatch.ride_assigned` `{ booking_id, driver_id, office_id }` → `booking.{id}` + `user.{id}` +
    winning `driver.{id}`
  - `dispatch.offer_expired` → the other `driver.{id}` rooms (dismiss their sheets)
  - `booking.status_changed` (`assigned`) → `booking.{id}` + `user.{id}`
- **App reaction:** rider shows the **assigned driver card**. The `ride_assigned` payload today
  carries only ids → the app fetches `GET /bookings/{id}` for driver + vehicle detail
  (**Q-4:** inline `driver{name,rating}` + `vehicle{model,plate,colour,class_label}` + `eta_minutes`
  to save the round-trip).

### S4 · Driver en route → pickup
| Driver action (REST) | Server → event on `booking.{id}` | Rider app |
|---|---|---|
| `POST /driver/trips/{b}/navigate-pickup` | `booking.status_changed` (`arriving`) | show "driver on the way" + ETA |
| `POST /driver/trips/{b}/location` (~5 s) | `driver.location` `{ driver_id, lat, lng, heading, eta_seconds }` | move the map pin, update ETA |
| `POST /driver/trips/{b}/arrived` | `booking.status_changed` (`arrived`) | "driver has arrived" |
- **Note (status enum):** the gateway emits **`arriving`**; the app also derives the en-route phase
  from the first `driver.location` tick if that status is missed. Add `heading` + `eta_seconds` to
  `driver.location` (currently only `lat/lng`).

### S5 · On trip
1. Driver: `POST /driver/trips/{b}/start` → `booking.status_changed` (`on_trip`) on `booking.{id}`.
2. **Live meter (metered classes):** during `on_trip` the server should emit **`booking.meter`**
   `{ booking_id, elapsed_s, distance_m, running_fare, currency }` on `booking.{id}` (**Q-2** — the
   driver side already has `meter:tick`; mirror one event to both rooms). The rider binds it to the
   live fare/time/distance readout.
3. **Chat:** rider `POST /trips/{id}/messages` → inserts `booking_chat_messages` (`from_type=rider`)
   → echo `booking.chat_message` to `booking.{id}` (driver gets the same event; numbers stay masked).
4. **Safety / SOS:** rider actions (share trip, SOS) are REST; SOS attaches the current
   `ride_bookings` context + the user's `safety_contacts` (no dedicated table).

### S6 · Completion & settlement
1. Driver: `POST /driver/trips/{b}/end`.
2. **Server:** finalize fare, set `status=completed`, `completed_at`, compute totals, capture payment
   / debit wallet, split commission, credit the driver.
3. **Events on `booking.{id}`:**
   - `booking.status_changed` (`completed`) `{ final_fare }`
   - then `ride.released` `{ booking_id }` (fare fully settled).
4. **App reaction:** show the completed state, then fetch `GET /bookings/{id}/receipt` for the
   breakdown, and **unsubscribe `booking.{id}`** after the terminal event.

### S7 · Rating
1. **REST:** `POST /trips/{id}/rating` (rate driver + office).
2. **Server:** insert `ride_ratings`, set `rated_at`.
3. **Event:** the driver gets `rating.received` on `driver.{id}` (dual rating). The rider may also
   receive `rating.received` on `user.{id}` when *it* is rated.

### S8 · Cancellation (three branches)
| Who | REST | Status → event | App |
|---|---|---|---|
| Rider (pre-pickup) | `POST /bookings/{id}/cancel {reason}` | `cancelled` (`source=rider`) | back to home; unsubscribe |
| Office declines (pre-assignment) | (office/admin action) | `cancelled` (`source=office`, `reason`) | "office unavailable" screen |
| Driver / no-show | (driver action) | `no_show` (or `cancelled`) | cancellation with reason |
- **Always include `source` (`rider|driver|office|system`) and a `reason`** on `cancelled` — the app
  shows the reason line and picks a different screen for an office decline vs a rider cancel. Any
  terminal status → unsubscribe `booking.{id}`.

### S9 · Scheduled (book-ahead) rides
1. `POST /bookings` (or `POST /scheduled`) with `scheduled_at` set → `ride_bookings.status=scheduled`.
2. The rider **does not** join `booking.{id}` yet. Near the pickup window the server flips the ride
   into dispatch → `booking.status_changed` (`matching`); the app then subscribes and the flow
   rejoins **S3**.
3. Manage via `GET/PATCH /scheduled/{id}` (PATCH bumps `change_revision`) and
   `DELETE /scheduled/{id}` (sets `cancelled_at`/`cancel_reason`).
- **Q-5:** confirm the "matching started" signal for scheduled rides so the app knows when to subscribe.

### S10 · Wallet & payments
| Trigger | REST | Event (on `user.{id}`) |
|---|---|---|
| View balance / ledger | `GET /wallet`, `GET /wallet/transactions` | — |
| Add card | `POST /payments/stripe/setup-intent` → confirm client-side (3DS) | — |
| Top up | `POST /wallet/topup` → `POST /payments/stripe/payment-intent` (3DS via `requiresAction`) | `wallet.credited` on success |
| Pay for a trip | PaymentIntent at settlement | `payment.succeeded` |
| Redeem promo | `POST /promos/redeem` | — |
- Raw PAN/CVV never reach Fleet Ride — cards are tokenized client-side. On `wallet.credited` /
  `payment.succeeded` the app just **re-fetches** the wallet.

### S11 · Notifications & push
1. On login the app registers its FCM/APNs token: `POST /devices { token, platform }`
   (`owner_type=rider`); `DELETE /devices/{token}` on logout.
2. **In-app center:** `GET /notifications`, `POST /notifications/{id}/read`,
   `POST /notifications/read-all`.
3. **Realtime:** emit **`notification.created`** on `user.{id}`
   `{ id, type, template_key, title, body, data, read_at, unread_count }` (**Q-6**) so the center and
   the unread badge update live. Share the `id` with the FCM push so foreground socket + background
   push **dedupe**. (If you prefer FCM-only, confirm and the app drops the socket path.)
- **Push delivery** is APNs/FCM (out of scope for REST); the app only *registers* the token.

### S12 · Support & complaints
- `GET/POST /tickets`, `GET /tickets/{id}` (thread), `POST /complaints`, `GET /help/articles`(+`/{id}`).
- Live: `support.message_created` (ticket reply) and `chat.message_created` (rider↔office) on
  `user.{id}` → the app re-fetches the thread.

### S13 · Reconnect & missed events (critical)
Redis is fire-and-forget. On **every** `connect`/reconnect the app:
1. **Re-subscribes** `user.{id}` (+ `booking.{activeId}` if a ride is live) — the server forgets rooms.
2. **Snapshots via REST** (`GET /bookings/{id}`, wallet, etc.) and reconciles the UI.
3. Resumes live events.
- **Server responsibility:** make every state fully re-derivable from REST so a missed event never
  leaves the app stuck. The trip FSM is designed to **bridge skipped statuses** (e.g. a missed
  `matching` is inferred), but authoritative reconciliation is always the REST snapshot.

### S14 · App background / foreground
- **Backgrounded, no active ride:** the app **drops the socket** (battery) — push notifications cover
  anything important.
- **Backgrounded, active ride:** the app **keeps the socket** (foreground service on Android /
  background-location on iOS) so `driver.location` + `booking.status_changed` keep flowing.
- **Foreground:** reconnect → S13.
- **Server:** nothing special, but this is *why* push (`notification.created` sharing the FCM id)
  matters — it's the only channel while the socket is down.

---

## Part 4 — The core ride as a sequence

```mermaid
sequenceDiagram
  participant R as Rider app
  participant API as REST API
  participant OB as Outbox→Redis→Gateway
  participant D as Driver app
  R->>API: POST /bookings (matching)
  API->>OB: booking.status_changed(matching) · dispatch.offer_created
  OB-->>R: user.{id}/booking.{id}: matching
  OB-->>D: driver.{id}: offer_created (countdown)
  D->>API: POST /driver/offers/{b}/accept   (atomic; loser=409)
  API->>OB: dispatch.ride_assigned · status_changed(assigned)
  OB-->>R: booking.{id}: ride_assigned + assigned
  R->>API: GET /bookings/{id}  (driver+vehicle detail)
  D->>API: navigate-pickup / location / arrived / start
  API->>OB: status_changed(arriving→arrived→on_trip) · driver.location · booking.meter
  OB-->>R: booking.{id}: live pin, ETA, meter
  D->>API: POST /driver/trips/{b}/end
  API->>OB: status_changed(completed) · ride.released
  OB-->>R: booking.{id}: completed + released
  R->>API: GET /bookings/{id}/receipt · POST /trips/{id}/rating
```

---

## Part 5 — The server-side trip FSM

```
matching ─► assigned ─► arriving ─► arrived ─► on_trip ─► completed
   │            │                                             
   └── cancelled (source=rider|office|system)   └── no_show / cancelled
scheduled ─► (pickup window) ─► matching …
```
`booking.status_changed.status` is the single field that drives the whole rider UI. **One enum,
published once**, is the biggest single dependency.

**⚠️ Decision Q-3 — reconcile your two docs.** The gateway guide (`realtime (1).md`) uses
`matching · assigned · arriving · arrived · on_trip · completed · cancelled`; the DriverX API
(`docs/specification/`) uses `arrived · in_progress` and adds `offered · no_show · scheduled`. Pick
**one** vocabulary (spelling of the in-progress state, whether `arriving` exists as a wire status,
and whether `offered/no_show/scheduled` are emitted to the rider). The rider currently accepts both
spellings so it can't stall, but production needs one truth.

---

## Part 6 — Open decisions & ownership

| # | Decision / gap | Owner | Blocks |
|---|---|---|---|
| **Q-2** | Emit `booking.meter` to the rider `booking.{id}` room (mirror driver `meter:tick`) | Backend | On-trip live fare UI |
| **Q-3** | Publish **one** `ride_bookings.status` enum (reconcile gateway ↔ DriverX) | Backend | Whole trip FSM |
| **Q-4** | Inline driver+vehicle+ETA in `dispatch.ride_assigned` (or confirm REST fetch) | Backend | Assignment card |
| **Q-5** | "Matching started" signal for scheduled rides | Backend | Scheduled subscribe timing |
| **Q-6** | Emit `notification.created` on `user.{id}` sharing the FCM id | Backend | Live notif center + badge |
| — | `driver.location`: add `heading` + `eta_seconds` | Backend | Pin rotation + countdown |
| — | Document payloads for `chat.message_created`, `support.message_created`, `wallet.credited`, `payment.succeeded`, `rating.received` | Backend | Account-level reactions |
| — | Persist `pickup_note` on the `POST /bookings` write path | Backend | Driver pickup hint |
| — | Confirm `booking.status_changed` also emits on `user.{id}` (not only `booking.{id}`) | Backend | Pre-subscribe reactions |

---

## Appendix — endpoint → event quick map

| REST write | Emits | Room(s) |
|---|---|---|
| `POST /bookings` | `booking.status_changed(matching)`, `dispatch.offer_created` | `user.{id}`,`booking.{id}` / `driver.{id}` |
| `POST /driver/offers/{b}/accept` | `dispatch.ride_assigned`, `status_changed(assigned)`, `dispatch.offer_expired` | `booking.{id}`,`user.{id}`,`driver.{id}` |
| `POST /driver/trips/{b}/navigate-pickup` | `status_changed(arriving)` | `booking.{id}` |
| `POST /driver/trips/{b}/location` | `driver.location` | `booking.{id}` |
| `POST /driver/trips/{b}/arrived` | `status_changed(arrived)` | `booking.{id}` |
| `POST /driver/trips/{b}/start` | `status_changed(on_trip)` (+ `booking.meter`…) | `booking.{id}` |
| `POST /trips/{id}/messages` | `booking.chat_message` | `booking.{id}` |
| `POST /driver/trips/{b}/end` | `status_changed(completed)`, `ride.released` | `booking.{id}` |
| `POST /bookings/{id}/cancel` | `status_changed(cancelled, source, reason)` | `booking.{id}`,`user.{id}` |
| `POST /trips/{id}/rating` | `rating.received` | `driver.{id}` (/ `user.{id}`) |
| `POST /wallet/topup` (settled) | `wallet.credited` | `user.{id}` |
| payment settled | `payment.succeeded` | `user.{id}` |
| notification raised | `notification.created` | `user.{id}` |

*Read alongside `openapi.v2.yaml` (shapes), `realtime (1).md` (gateway), `RIDER_REALTIME_EVENTS.md`
(payloads + open asks).*
