# Fleet Ride — Backend Handoff Bundle

> Single-file bundle for the backend developer. Combines the master requirements with the
> realtime, social-auth, and push/background specs. Assembled from the individual docs in
> `docs/` — if anything looks out of date, those source files are authoritative.

**Contents**

1. [Backend Requirements (master)](#part-1--backend-requirements-master)
2. [Realtime App Requirements](#part-2--realtime-app-requirements)
3. [Social Auth Setup](#part-3--social-auth-setup)
4. [Background / Push Setup](#part-4--background--push-setup)

---

<a id="part-1--backend-requirements-master"></a>
# Part 1 — Backend Requirements (master)


**From:** Rider app team → **Backend team.** Everything the rider app needs: **REST APIs**
(requests + responses), the **realtime socket** (client action → server event → payload), and **push
notifications**. This is the consolidated handoff; deeper specs live in the companion docs linked per
section.

- REST contract (authoritative schemas): **`docs/openapi.v2.yaml`** + `docs/API_MODEL_MAPPED.md`
- Realtime deep spec + open questions: **`docs/REALTIME_APP_REQUIREMENTS.md`**
- Social auth: **`docs/SOCIAL_AUTH_SETUP.md`** · Push/background: **`docs/BACKGROUND_SETUP.md`**

---

## 0. Conventions (apply to every REST call)

- **Base URL:** `https://<host>/v1` (build-time `API_BASE_URL`).
- **Headers the app sends:** `Authorization: Bearer <access_token>`, `Accept-Language: en|ar`,
  `Content-Type: application/json`, and `Idempotency-Key: <uuid>` on unsafe writes (bookings,
  payments). Multi-country builds also send `X-Country: <ISO-2>`.
- **Response envelope — every endpoint returns this shape:**
  ```jsonc
  { "status": true, "statusCode": 200, "message": "OK", "locale": "en", "data": <payload>,
    "error": null, "meta": { /* pagination: cursor/nextCursor when applicable */ } }
  ```
  The app reads `status` (ok?), `message` (error text), `data` (typed), `meta` (paging).
- **Auth failure:** `401` triggers a `POST /auth/refresh`; if that fails the app signs out.
- **Money:** integer **minor units** + `currency_code` (e.g. `fare_minor: 2400`, `QAR`).
- **Paginated lists:** accept `?limit=&cursor=`, return `data: [...]` + `meta.nextCursor`.

---

## 1. REST APIs

Method + path + purpose. **NEW** = not in the original contract; **CHG** = changed. Full request/
response schemas are in `openapi.v2.yaml`; the new/changed ones are detailed in §1.9.

### 1.1 Auth (`oauth_*`)
| Method | Path | Purpose |
|---|---|---|
| POST | `/auth/otp/request` | Send OTP → `{ challengeId, expiresInSec, length }` |
| POST | `/auth/otp/verify` | Verify OTP → **AuthSession** |
| POST | `/auth/register` | Register → **AuthSession** |
| POST | `/auth/social` | **NEW** — Google/Apple sign-in → **AuthSession** (see §1.9a) |
| POST | `/auth/refresh` | Rotate tokens → `{ accessToken, refreshToken }` |
| POST | `/auth/phone/change` | Start phone change → OTP challenge |
| POST | `/auth/logout` | Revoke current token |
| DELETE | `/account` | Delete account |

### 1.2 Profile (`/me`)
| Method | Path | Purpose |
|---|---|---|
| GET | `/me` | Identity + wallet balance |
| GET/POST | `/me/places` · DELETE `/me/places/{id}` | Saved places CRUD |
| GET/POST | `/me/safety-contacts` · DELETE `/me/safety-contacts/{id}` | Safety contacts + `auto-share` |
| GET | `/me/notifications-prefs` (+ update) | Notification prefs |
| GET | `/me/privacy` (+ update) | Privacy toggles |

### 1.3 Marketplace (offices)
| Method | Path | Purpose |
|---|---|---|
| POST | `/offices/search` | Rank offices for a route → offers `{ office, fareMinor, etaMinutes, why, recommended }` |
| GET | `/offices/{id}` | Office profile |
| GET | `/me/favorites` · POST/DELETE `/me/favorites/{officeId}` | Favorites |

### 1.4 Booking (pre-ride)
| Method | Path | Purpose |
|---|---|---|
| GET | `/catalog/services` | Admin service catalog (drives Home) |
| GET | `/catalog/classes?service=` | Sub-classes for a service |
| GET | `/places/suggest?q=&lat=&lng=` | Destination autocomplete |
| POST | `/routes/estimate` | Distance/ETA/fares for pickup→dropoff |
| POST | `/geocode/reverse` | Coords → address |
| **POST** | **`/bookings`** | **NEW/required** — create the ride (see §1.9c, carries `pickup_note`) |
| **POST** | **`/bookings/{id}/cancel`** | **NEW/required** — rider cancels |

> The live ride is currently driven over the socket in the app's mock; in production **ride creation
> and lifecycle actions are REST** (they emit the socket events in §2). See §2.3 for the full action map.

### 1.5 Trips (history, receipt, chat, rating)
| Method | Path | Purpose |
|---|---|---|
| GET | `/trips?status=` | History (upcoming/completed/cancelled) |
| GET | `/trips/{id}` | Receipt / trip detail |
| POST | `/trips/{id}/rating` | Rate the trip/driver |
| POST | `/trips/{id}/lost-item` | Report lost item → ticket ref |
| GET/POST | `/trips/{id}/messages` | In-ride chat history + send (live via socket) |

### 1.6 Wallet & Payments
| Method | Path | Purpose |
|---|---|---|
| GET | `/wallet` · `/wallet/transactions` | Balance + ledger |
| POST | `/wallet/topup` | Top up |
| GET/POST | `/payment-methods` · DELETE `/payment-methods/{id}` | Cards/methods |
| POST | `/payments/stripe/setup-intent` · `/payments/stripe/payment-intent` | Stripe intents |
| GET | `/promos` · POST `/promos/redeem` | Promotions |

### 1.7 Support & Scheduled & Corporate
| Method | Path | Purpose |
|---|---|---|
| GET/POST | `/tickets` · GET `/tickets/{id}` | Support tickets |
| GET | `/help/articles` · `/help/articles/{id}` | Help center |
| POST | `/complaints` | File a complaint |
| GET/POST | `/scheduled` · GET/DELETE `/scheduled/{id}` | Scheduled rides |
| GET | `/corporate/invoices` | B2B invoices |
| GET/POST | `/family/members` · DELETE `/family/members/{id}` | Family accounts |

### 1.8 Notifications & Devices
| Method | Path | Purpose |
|---|---|---|
| GET | `/notifications?unread=` | Inbox (also seeds the badge) |
| POST | `/notifications/{id}/read` · `/notifications/read-all` | Mark read |
| **POST** | **`/devices`** | **NEW** — register FCM/APNs push token (see §1.9b) |
| DELETE | `/devices/{token}` | Unregister on logout |

### 1.9 New/changed request & response detail

**a) `POST /auth/social`** — Google/Apple sign-in / sign-up.
```jsonc
// request
{ "provider": "google" | "apple",
  "idToken": "<provider OIDC id token>",
  "authorizationCode": "<apple only, single-use>",   // optional
  "email": "…", "fullName": "…",                       // optional (first Apple auth only)
  "country": "QA" }
// response data = AuthSession (SAME shape as /auth/otp/verify)
{ "accessToken": "…", "refreshToken": "…", "user": { "id": "…", "firstName": "…", … } }
```
Backend: verify `idToken` (Google: audience = server client id; Apple: Apple public keys), find-or-create
the user, upsert email/name when present.

**b) `POST /devices`** — push token.
```jsonc
// request
{ "token": "<FCM/APNs token>", "platform": "ios" | "android" | "web" }
// response data = { "id": "…", "token": "…", "platform": "…", "lastSeenAt": "…" }
```

**c) `POST /bookings`** — create a ride (carries the driver pickup note).
```jsonc
// request (fields the app sends)
{ "service": "ride", "service_class": "City", "pricing_style": "meterEstimate",
  "office_id": "hala",
  "pickup_lat": 25.276, "pickup_lng": 51.52, "pickup_title": "…", "pickup_note": "north gate",
  "dropoff_lat": 25.273, "dropoff_lng": 51.608, "dropoff_title": "…",
  "scheduled_at": null, "idempotency_key": "…" }
// response data = RideBooking (status starts at `matching`)
```
`pickup_note` (≤255, nullable) already exists on `ride_bookings` + the `RideBooking` schema — just
**persist it on create** and surface it to the driver app.

---

## 2. Realtime socket (WebSocket / Socket.IO)

Full spec + open questions: **`docs/REALTIME_APP_REQUIREMENTS.md`**. Summary of what the backend must
provide:

### 2.1 Connection
- `wss://<host>:6002` (default port). Handshake **auth**: `{ token: <access_token>, country: <ISO-2> }`.
- On missing/invalid token → emit `unauthorized` and disconnect.

### 2.2 Channels (subscribe/unsubscribe are **acknowledged**)
| Channel | Who | When |
|---|---|---|
| `user.{userId}` | rider | whole session (account-level events) |
| `booking.{bookingId}` | rider | only the active ride; unsubscribed on terminal status |

Server authorizes each `subscribe` (only own `user.{id}`, and a `booking.{id}` the rider is party to)
and replies `{ authorized: true|false }`. Events are emitted as **`"{channel}:{eventType}"`**.

### 2.3 Client action (REST) → resulting event(s)
The socket is **receive-only for domain events**; actions are REST and the backend emits the event:
| Rider action | REST call | Emits |
|---|---|---|
| Book a ride | `POST /bookings` | `booking.status_changed(matching)`, then dispatch |
| Cancel | `POST /bookings/{id}/cancel` | `booking.status_changed(cancelled)` |
| In-ride message | `POST /trips/{id}/messages` | `booking.chat_message` |
| Rate | `POST /trips/{id}/rating` | `rating.received` (to driver) |

### 2.4 Server → client event catalog
**On `booking.{id}`:**
| eventType | Payload | App use |
|---|---|---|
| `booking.status_changed` | `{ booking_id, status, office_id, source, reason?, final_fare? }` | Drive trip FSM |
| `dispatch.ride_assigned` | `{ booking_id, driver_id, driver{name,rating}, vehicle{model,plate,colour,class_label}, eta_minutes }` | Show driver card |
| `driver.location` | `{ driver_id, lat, lng, heading, eta_seconds }` | Move map pin |
| `booking.meter` | `{ booking_id, elapsed_s, distance_m, running_fare, currency }` | Live fare (**Q-2**) |
| `booking.chat_message` | `{ booking_id, message_id, sender_role, text, created_at }` | Append chat |
| `ride.released` | `{ booking_id }` | Show receipt |

**On `user.{id}`:**
| eventType | Payload | App use |
|---|---|---|
| `booking.status_changed` | (mirror of above) | React before joining booking channel |
| `notification.created` | see §3 | Live inbox/badge |
| `chat.message_created` | `{ conversation_id, message_id, sender_role, preview, created_at }` | Office thread |
| `support.message_created` | `{ ticket_id, message_id, created_at }` | Support reply |
| `wallet.credited` | `{ amount, currency, balance_after, reason, ref_id }` | Refresh balance |
| `payment.succeeded` | `{ payment_id, booking_id?, amount, currency, method }` | Confirm payment |
| `rating.received` | `{ booking_id, stars, from_role }` | "You were rated" |

**`status` enum** (drive the FSM): `matching → assigned → arriving → arrived → on_trip →
completed`, plus terminal `cancelled` / `rejected`. On cancel/reject include `source`
(`rider|driver|office|system`) + human `reason`. **Please confirm the full enum — see Q-3.**

---

## 3. Push notifications (FCM + APNs)

Deep spec: **`docs/BACKGROUND_SETUP.md`**. The app is wired for FCM (Firebase project `fleet-bfb36`,
package `com.codepac.fleetapp`) and registers its token via `POST /devices`.

- **Send a push for the same events as the socket** so the rider is reachable when the app is
  backgrounded/closed: driver-assigned, arriving, completed/receipt, wallet, and any
  `notification.created`.
- **Payload — carry a `data.id` equal to the in-app notification id** so the foreground socket event
  and the push **dedupe** (app shows it once):
  ```jsonc
  {
    "notification": { "title": "Trip completed", "body": "Your fare was 24.00 QAR" },
    "data": { "id": "ntf_4100", "type": "receipt", "tripId": "4100" }   // id REQUIRED
  }
  ```
- **`notification.created` (socket, `user.{id}`) payload** — same fields, 1:1 with the inbox model:
  ```jsonc
  { "id": "ntf_4100", "type": "trip|receipt|promo|wallet|general",
    "template_key": "ride_completed", "title": "…", "body": "…",
    "data": { "tripId": "4100" }, "read_at": null, "created_at": "…",
    "unread_count": 3 }
  ```
- iOS needs an **APNs auth key** uploaded to Firebase; Android is ready.

---

## 4. Decisions we need from you (blocking items ranked)

| # | Item | Priority |
|---|---|---|
| Q-2 | **Live fare/meter** during `on_trip` — will you emit `booking.meter`? (else app can't show a running fare) | **High** |
| Q-3 | **Complete `booking` status enum** + `source` + `reason` on cancel/reject | **High** |
| Q-4 | Driver+vehicle+ETA: inline in `dispatch.ride_assigned` **or** guaranteed on `GET /bookings/{id}` | **High** |
| — | Implement `POST /auth/social` (verify Google/Apple `idToken`) | **High** |
| — | Implement `POST /bookings` + `/cancel` and persist `pickup_note` | **High** |
| — | Send FCM/APNs for socket events, sharing the notification `id` for dedupe | **High** |
| — | Documented payloads for `booking.chat_message`, `wallet.credited`, `payment.succeeded`, `rating.received`, `chat.message_created`, `support.message_created` | Med |
| Q-6 | Emit `notification.created` on `user.{id}` (spec §3) | Med |
| Q-1 | One active `booking.{id}` per rider, or several concurrent? | Med |
| Q-5 | Scheduled ride: when does it enter `matching` / is there an event? | Med |

Full rationale for each Q is in `docs/REALTIME_APP_REQUIREMENTS.md` §4–§8.

---

<a id="part-2--realtime-app-requirements"></a>
# Part 2 — Realtime App Requirements


**From:** Rider app team
**To:** Backend / realtime-gateway team
**Re:** `docs/realtime (1).md` (FleetOS Realtime Integration Guide)

We reviewed the integration guide. The socket contract (`{channel}:{eventType}`,
subscribe/unsubscribe, snapshot-on-connect) works for us. This document lists **exactly what the
rider app consumes**, the **payload fields the UI binds to**, the **subscribe/unsubscribe
lifecycle** (join / leave), the **end-to-end scenarios**, and the **open questions** where the guide
is silent or thinner than the app needs.

The rider app's trip screen is driven by a finite-state machine
(`lib/core/data/trip_state.dart`) with these phases:

```
idle → requested → officeConfirmed → assigning → accepted → arriving → arrived → onTrip → completed
                                                                    ↘ cancelled / rejected (terminal)
scheduled  (booked-ahead, waiting for its pickup window)
```

Every realtime event ultimately has to move this FSM and/or fill in driver / vehicle / office / fare
detail. That is the lens for everything below.

---

## 1. Connection (what the app sends on handshake)

| Field | Value | Notes |
|---|---|---|
| `auth.token` | Passport `access_token` from `POST /api/v1/auth/otp/verify` | Read fresh on every (re)connect so login/refresh is picked up |
| `auth.country` | ISO-2 code, same as `X-Country` REST header | Needed for the booking-channel shard |

- Transport: `websocket` only, Socket.IO `^4`, auto-reconnect on.
- On `unauthorized` → force re-login. On repeated `connect_error` after a token change → reconnect
  with the new token.

**✅ No change needed** — matches the guide.

---

## 2. Channels the rider joins / leaves (subscribe lifecycle)

| Channel | Join (subscribe) when | Leave (unsubscribe) when |
|---|---|---|
| `user.{myUserId}` | Right after `connect`, for the **whole session** | Never during a session (only on logout / disconnect) |
| `booking.{bookingId}` | The moment a booking becomes **active** (right after `POST /bookings` succeeds, or when a snapshot reports an active booking) | On any **terminal** `booking.status_changed` (`completed` / `cancelled` / `rejected`) **and** after `ride.released` receipt is shown |

Rules the app follows (please confirm the server matches):

1. **Re-subscribe on every (re)connect.** We re-send `subscribe` for `user.{id}` and, if a booking is
   active, `booking.{id}` after each `connect`.
2. **Snapshot-on-connect.** Immediately after (re)connect we call `GET /bookings/{activeId}` (and
   wallet balance) to reconcile, because Redis pub/sub does not replay missed events.
3. Subscribe is **acknowledged** — we only treat a channel as joined when the ack returns
   `{ authorized: true }`.

**Open Q-1:** Is there **at most one active booking** per rider at a time, or can a rider have
several concurrent `booking.{id}` channels (e.g. a scheduled ride + a live ride)? The app currently
assumes one active `booking.{id}`; if multiple are possible we need to fan out subscriptions.

---

## 3. Events the rider app REQUIRES (with the payload we bind to)

Legend: **✅ documented & sufficient** · **⚠️ documented but payload too thin** · **❌ needed, not
specified**. "Re-fetch" = we call REST for authoritative detail after the event; "Payload" = we'd
prefer the field pushed inline to avoid a round-trip.

### 3.1 Booking lifecycle

| eventType | Channel | App uses it to | Payload we need |
|---|---|---|---|
| `booking.status_changed` | `user.{id}` + `booking.{id}` | Move the FSM to the next phase | `{ booking_id, status, office_id, source, at }` — **status enum must be complete, see §4** |
| `dispatch.ride_assigned` | `booking.{id}` + `user.{id}` | Enter `accepted`; show the assigned driver + vehicle card | ⚠️ guide gives only `{ booking_id, driver_id, office_id }`. We need **driver + vehicle + ETA** (see §5) either inline or via a confirmed REST snapshot |
| `driver.location` | `booking.{id}` | Move the driver pin on the map; update pickup ETA | ⚠️ guide gives `{ driver_id, lat, lng }`. Please also add **`heading`/`bearing`** (pin rotation) and **`eta_seconds`** (arriving countdown). See §6 |
| `ride.released` | `booking.{id}` | Trip fully settled → show receipt | `{ booking_id }` is enough; we fetch `GET /bookings/{id}/receipt` |

### 3.2 Live fare / meter — **NOT in the guide, but the app needs it** ❌

The rider's on-trip screen shows a **live running fare + elapsed time + distance** for metered
services (Ride / Premium). The current app model expects a per-tick update (`MeterReading { time,
distanceKm, total }`). The realtime guide has **no meter event**.

**Open Q-2 (blocking for on-trip UI):** During `on_trip`, how does the rider get the running fare?
Options we can support — please pick one:
- **(A)** Backend emits `booking.meter` on `booking.{id}` with
  `{ booking_id, elapsed_s, distance_m, running_fare, currency }` (preferred — authoritative).
- **(B)** We derive elapsed/distance client-side from `driver.location` ticks and price locally
  (needs the tariff: base, per-km, per-min, currency).
- **(C)** No live meter; show only the final fare at `completed`. (Degrades the UX.)

### 3.3 Messaging

| eventType | Channel | App uses it to | Payload we need |
|---|---|---|---|
| `booking.chat_message` | `booking.{id}` | Append in-trip message (rider ↔ driver) | ❌ undocumented. Need `{ booking_id, message_id, sender, sender_role: "driver"\|"rider", text, created_at }` so we can render + dedupe without a full re-fetch |
| `chat.message_created` | `user.{id}` | New message in a rider ↔ office thread | ❌ undocumented. Need `{ conversation_id, message_id, sender_role, preview, created_at }` |
| `support.message_created` | `user.{id}` | New reply on a support ticket | ❌ undocumented. Need `{ ticket_id, message_id, created_at }` |

### 3.4 Money & rating

| eventType | Channel | App uses it to | Payload we need |
|---|---|---|---|
| `wallet.credited` | `user.{id}` | Refresh balance; toast the top-up/refund | ❌ undocumented. Need `{ amount, currency, balance_after, reason, ref_id }` |
| `payment.succeeded` | `user.{id}` | Confirm a payment intent (e.g. card top-up, ride payment) | ❌ undocumented. Need `{ payment_id, booking_id?, amount, currency, method }` |
| `rating.received` | `user.{id}` | Show "you were rated" (dual rating) | ❌ undocumented. Need `{ booking_id, stars, from_role: "driver" }` |

---

## 3.5 Coverage & forward-compatibility

**What this document covers:** every **rider-facing** event the gateway emits **today** — all 10 of
them (`booking.status_changed`, `dispatch.ride_assigned`, `driver.location`, `ride.released`,
`booking.chat_message`, `chat.message_created`, `support.message_created`, `wallet.credited`,
`payment.succeeded`, `rating.received`) plus the connection/control messages (`unauthorized`,
`connect`/`disconnect`/`connect_error`, `subscribe`/`unsubscribe` acks).

**Deliberately out of scope** (not consumed by the rider app):
- Driver-only: `dispatch.offer_created`, `dispatch.offer_expired`, `presence.changed`,
  `wallet.payout`.
- Office/admin-only (web session, not this token flow): `subscription.*`, office dispatch/presence.

**Not future-proof — by design of the current guide.** The guide enumerates no future events, no
schema version, and no "unknown event" policy. To keep the rider app from breaking as the catalog
grows, we ask the backend to commit to:

1. **Additive-only changes.** New optional fields on existing payloads and brand-new `eventType`s are
   fine; **renaming or removing** a field/event is a breaking change and needs a heads-up. The app
   already treats every field as optional and ignores unknown events safely.
2. **A schema/version signal.** Either a top-level `v` on payloads or a documented changelog, so we
   can tell an intentional shape change from a bug.
3. **A canonical event list** we can diff against per release (even just the table in the guide,
   kept current).

**Two rider surfaces the guide omits — please confirm:**

| # | Surface | Question |
|---|---|---|
| Q-6 | **Generic in-app notifications** | The app has a notifications inbox (`AppNotification`, types `trip`/`receipt`/`general`…). Is there a socket event (e.g. `notification.created` on `user.{id}`) so the bell badge live-updates in the foreground, or do these arrive **only via FCM push**? If FCM-only, the badge won't update without a re-fetch. |
| Q-7 | **Rider membership / subscription** | `subscription.*` is scoped to office dashboards in the guide. If riders have a membership/plan, is there a rider-facing subscription event (activated / expired / renewed) on `user.{id}`? |

---

## 3.6 In-app notification event — proposed `notification.created` ❌

**Why we need it.** The rider app has a notification inbox (`AppNotification`) backed by REST
(`GET /notifications`, `POST /notifications/{id}/read`, `POST /notifications/read-all`) and a bell
badge with an unread count. Push (FCM) covers the **background** case. But while the app is in the
**foreground**, nothing on the socket tells us a new notification landed — so the badge and inbox go
stale until the user manually pulls to refresh. We need a `user.{id}` event to close that gap.

**Channel:** `user.{id}` **Event:** `notification.created`

**Payload we need** (fields map 1:1 to our `AppNotification` model, so we can render the row without a
round-trip; snake_case or camelCase both accepted by our parser):

```jsonc
{
  "id": "9f3c…",              // notification id — REQUIRED (used to dedupe vs the FCM push)
  "type": "trip",             // trip | receipt | promo | wallet | general … drives the row icon
  "template_key": "ride_completed",
  "title": "Trip completed",
  "body": "Your fare was 24.00 QAR",
  "data": { "tripId": "4100" }, // arbitrary JSON for deep-linking
  "read_at": null,               // always null on create
  "created_at": "2026-07-15T09:12:03Z",
  "unread_count": 3              // rider's total unread AFTER this one — lets us set the badge without recounting
}
```

**How the app behaves on it:**
1. Set the bell **badge** from `unread_count` (fallback: increment locally if absent).
2. If the inbox screen is open, **prepend** the row from the payload (no re-fetch).
3. Optionally show a lightweight in-app toast/banner using `title`/`body`.
4. `data` drives the deep-link (e.g. open trip `4100`).

**Dedup with FCM (important):** the same notification may arrive **twice** — once via this socket
event (foreground) and once via FCM push (if backgrounded during a race). Both **must carry the same
`id`** so we can show it once. Please guarantee that.

**Snapshot-on-connect:** after (re)connect we call `GET /notifications?unread=true` (already exists)
to reconcile the badge, then let `notification.created` keep it live. No extra REST needed.

**Open Q-6 (restated):** Can the gateway emit `notification.created` on `user.{id}` with the payload
above, sharing the `id` with the FCM push? If notifications are FCM-only by design, confirm that so
we fall back to polling `GET /notifications` on foreground/resume instead.

### Scenario H — New in-app notification (foreground)

```
… (backend writes an app_notification for the rider) …
srv → user.{id}:notification.created  { id, type, title, body, data, unread_count }
app: badge = unread_count; if inbox open, prepend row; optional toast; wire deep-link from data
user taps → app: POST /notifications/{id}/read   → badge decrements
```

---

## 3.7 Ride-request payload — `pickup_note` (client → server) ✅

The rider can leave a free-text note for the driver about the pickup point (e.g.
"north gate, blue door"). The app collects it on the destination screen and sends it with the ride
request.

**Field:** `pickup_note` — `string`, optional/nullable, ≤ 255 chars. Omitted when empty.

**Where it travels:**
- **Now** (socket-simulated action flow): included in the `request_ride` / `schedule_ride` frame.
- **After the REST migration** (actions → REST, see §7): a field on the `POST /bookings` request body.

**Backend status — already in place, no migration needed:**

| Layer | Location | State |
|---|---|---|
| Column | `ride_bookings.pickup_note` — `create_ride_bookings_table.php` (`$table->string('pickup_note')->nullable()`) | ✅ exists |
| Model | `RideBooking::$fillable` includes `pickup_note` | ✅ exists |
| Schema | `RideBooking.pickup_note` in `openapi.v2.yaml` + `API_MODEL_MAPPED.md` | ✅ documented (response) |

**Only ask of the backend:** ensure the ride-request **write path accepts and persists**
`pickup_note` (validation `nullable|string|max:255`), and that the **driver app surfaces it** on the
trip screen. It is already a fillable column and appears on the `RideBooking` response, so it will
round-trip on `GET /bookings/{id}` / receipts once written.

---

## 4. Status enum alignment (the most important gap) ⚠️

`booking.status_changed.status` drives our whole FSM, so we need the **exact, complete set of
values**. The guide's example lists
`matching → assigned → arriving → arrived → on_trip → completed/cancelled`, but our FSM has **two
extra pre-driver states** and a distinct **rejected** terminal that aren't covered:

| App phase | Expected `status` value | In guide? | Question |
|---|---|---|---|
| `requested` | `matching` (or `pending`?) | ~ | Which string does the server send right after `POST /bookings`? |
| `officeConfirmed` | ❓ | **No** | Is there a status for "an office picked up the request but no driver yet"? Or do we collapse this into `matching`? |
| `assigning` | ❓ | **No** | Is "searching for a driver / offers out" a distinct status, or still `matching`? |
| `accepted` | `assigned` | ✅ | Comes with `dispatch.ride_assigned` — confirm both fire |
| `arriving` | `arriving` | ✅ | |
| `arrived` | `arrived` | ✅ | |
| `onTrip` | `on_trip` | ✅ | |
| `completed` | `completed` | ✅ | terminal → we unsubscribe |
| `cancelled` | `cancelled` | ✅ | Need `source` (rider vs office vs system) + a `reason` string for the UI |
| `rejected` | ❓ | **No** | When an office rejects **before** assignment — is this `cancelled` with `source=office`, or a separate `rejected` status? We show a different screen for reject vs cancel |

**Open Q-3:** Please send the **authoritative enum** of all `status` values and, for `cancelled` /
`rejected`, include **`source`** (`rider` | `driver` | `office` | `system`) and a human-readable
**`reason`** — the app shows the reason line to the rider.

---

## 5. Driver & vehicle detail (assignment card) ⚠️

At `accepted`, the rider sees a driver card. The app model needs:

```
driver  : { name, rating }
vehicle : { model, plate, colour, class_label }
eta_minutes_to_pickup
```

`dispatch.ride_assigned` currently carries only `driver_id`. Two acceptable paths — please confirm
one:
- **(A)** Include a `driver` + `vehicle` snapshot inline in `dispatch.ride_assigned`, **or**
- **(B)** Guarantee `GET /bookings/{id}` returns those fields once status is `assigned`, and we
  fetch on the event.

**Open Q-4:** Which one, and if (B), does `GET /bookings/{id}` already return `driver.name`,
`driver.rating`, `vehicle.{model,plate,colour,class_label}`, and a pickup ETA?

---

## 6. `driver.location` enrichment ⚠️

We render the driver as a moving, **rotating** pin and a live pickup countdown. Please add to the
payload:

```
{ driver_id, lat, lng, heading /* 0–359° */, eta_seconds /* to pickup during arriving, to dropoff during on_trip */, at /* server ts */ }
```

`heading` lets us rotate the pin; `eta_seconds` avoids us re-deriving ETA from raw coordinates.
Throttle guidance from the guide (~5 s) is fine.

---

## 7. End-to-end scenarios (the sequences we implement against)

### Scenario A — Happy path (immediate ride)

```
Rider: POST /bookings                → app: FSM idle → requested, subscribe booking.{id}
srv → booking.status_changed(matching)          → requested / assigning
srv → dispatch.ride_assigned + status_changed(assigned)
                                     → accepted; app fetches driver+vehicle, shows card
srv → booking.status_changed(arriving)          + driver.location…  → arriving (map + ETA)
srv → booking.status_changed(arrived)           → arrived
srv → booking.status_changed(on_trip)           → onTrip
srv → booking.meter…  (Q-2)                      → live fare ticks
srv → booking.status_changed(completed)         → completed
srv → ride.released                             → fetch receipt; UNSUBSCRIBE booking.{id}
```

### Scenario B — Rider cancels before pickup

```
(any pre-onTrip active phase)
Rider: POST /bookings/{id}/cancel
srv → booking.status_changed(cancelled, source=rider, reason)
                                     → cancelled screen; UNSUBSCRIBE booking.{id}
```

### Scenario C — Office/driver rejects before assignment

```
requested / assigning
srv → booking.status_changed(rejected OR cancelled+source=office, reason)
                                     → "no office/driver available" screen; UNSUBSCRIBE
```
*(Depends on Q-3: we need to tell reject apart from cancel.)*

### Scenario D — Scheduled (book-ahead) ride

```
Rider: POST /bookings (scheduled_for)  → FSM scheduled (we DON'T subscribe booking yet? — Q-5)
… at pickup window …
srv → booking.status_changed(matching)          → subscribe booking.{id}, requested/assigning
… then same as Scenario A …
```
**Open Q-5:** For a scheduled ride, when should the app **subscribe** `booking.{id}` — at creation,
or only when the pickup window opens and matching starts? And is there an event when a scheduled
ride transitions into matching, or must the app poll?

### Scenario E — Reconnect mid-trip (missed events)

```
socket drops during on_trip → Socket.IO reconnects
app on 'connect':  re-subscribe user.{id} + booking.{activeId}
app: GET /bookings/{activeId}  → reconcile phase, driver, fare  (snapshot)
resume live events
```
This is why every payload can be treated as a **hint**; REST is authoritative. ✅ matches the guide.

### Scenario F — Chat during trip

```
Rider: POST /bookings/{id}/chat        → optimistic append
srv → booking.chat_message (to driver) ; driver replies →
srv → booking.chat_message (to rider)  → append (need payload per §3.3 to dedupe vs optimistic)
```

### Scenario G — Wallet / payment / rating (account-level, no active booking needed)

```
srv → wallet.credited      → refresh balance + toast
srv → payment.succeeded    → confirm intent UI
srv → rating.received      → show "driver rated you"
```

---

## 8. Summary — what we need back from the backend

| # | Item | Priority |
|---|---|---|
| Q-1 | One active `booking.{id}` at a time, or can there be several? | Med |
| Q-2 | **Live meter/fare** during `on_trip` — pick option A/B/C (§3.2) | **High (blocks on-trip UI)** |
| Q-3 | **Complete `status` enum** + `source` + `reason` on cancel/reject; distinguish `officeConfirmed`/`assigning`/`rejected` (§4) | **High** |
| Q-4 | Driver + vehicle + ETA: inline in `ride_assigned` or via `GET /bookings/{id}` (§5) | **High** |
| Q-5 | Scheduled ride: when to subscribe + is there a "matching started" event (§4 D) | Med |
| Q-6 | Emit `notification.created` on `user.{id}` (spec in §3.6), sharing `id` with FCM — or confirm FCM-only | Med |
| Q-7 | Rider membership/subscription events on `user.{id}`? (§3.5) | Low |
| — | Commit to additive-only changes + a version/changelog signal (§3.5) | Med |
| — | Persist `pickup_note` on the ride-request write path (column already exists) (§3.7) | Low |
| — | Documented payloads for `booking.chat_message`, `chat.message_created`, `support.message_created`, `wallet.credited`, `payment.succeeded`, `rating.received` (§3.3–3.4) | **High** |
| — | `driver.location`: add `heading` + `eta_seconds` (§6) | Med |
| — | Confirm the socket **also** emits `booking.status_changed` on `user.{id}` (not just `booking.{id}`) so we can react before joining the booking channel | Med |

Once Q-2/Q-3/Q-4 and the missing payloads are pinned down, we can finalize the rider socket layer
against the real gateway.

---

<a id="part-3--social-auth-setup"></a>
# Part 3 — Social Auth Setup


How to take the scaffolded Google/Apple sign-in from the **offline mock** to a **live** flow.

## Architecture (already wired)

```
LoginScreen ─▶ AuthController.signInWithProvider(provider)
                 │  ├─ SocialAuthService.signIn(provider)  → SocialCredential {idToken, code?, email?, name?}
                 │  │     • MockSocialAuthService   (useMock=true — offline, no native config)
                 │  │     • RealSocialAuthService   (useMock=false — google_sign_in + sign_in_with_apple)
                 │  └─ AuthRepository.socialSignIn(...)     → POST /auth/social → AuthSession
                 └─ SessionController.setTokens(...)        (persists the session)
```

- **Selection is automatic:** `socialAuthServiceProvider` returns the mock while `apiConfigProvider.useMock`
  is true, and `RealSocialAuthService` on a live build (`--dart-define=LIVE=true`).
- **Packages:** `google_sign_in: ^6.2.1`, `sign_in_with_apple: ^6.1.4` (in `pubspec.yaml`).

## What you must provide

| Item | Where it goes | Notes |
|---|---|---|
| Google **web/server** client id | `--dart-define=GOOGLE_SERVER_CLIENT_ID=…` → `AppEnv.googleServerClientId` → `googleServerClientIdProvider` | Token audience the backend verifies. Required for an `idToken` on Android. |
| Google **iOS** client id (reversed) | `ios/Runner/Info.plist` → `CFBundleURLTypes` (replace `REVERSED_CLIENT_ID`) | From the iOS OAuth client / `GoogleService-Info.plist` (`REVERSED_CLIENT_ID`). |
| Google **Android** SHA-1/SHA-256 | Google Cloud / Firebase console (Android OAuth client) | Register debug + release signing certs. No manifest change needed. |
| Apple **App ID** capability | Apple Developer portal → App ID → enable "Sign in with Apple" | Bundle id `com.codepac.fleetapp`. |
| Apple **Service ID** + key | Apple Developer portal (for the backend token verification / Android web flow) | Backend needs the Apple public keys to verify `identityToken`. |

## iOS (done in the repo)

- ✅ `ios/Runner/Runner.entitlements` created with `com.apple.developer.applesignin = [Default]`.
- ✅ `CODE_SIGN_ENTITLEMENTS = Runner/Runner.entitlements` added to all three Runner build configs
  (Debug/Release/Profile) in `project.pbxproj`.
- ✅ `Info.plist` has a `CFBundleURLTypes` entry for the Google callback.

**Remaining (needs your account, do in Xcode once):**
1. Open `ios/Runner.xcworkspace` → Runner target → **Signing & Capabilities**. Confirm
   **Sign in with Apple** appears (it reads the entitlement). If signing complains, let Xcode
   register the capability on your App ID.
2. Replace `REVERSED_CLIENT_ID` in `Info.plist` with your real reversed iOS client id.
3. `cd ios && pod install` (the plugins add pods).

## Android

- **Google:** no manifest change. Provide `GOOGLE_SERVER_CLIENT_ID` and register the app's SHA-1/-256
  in the Google console. That's enough for `idToken`.
- **Apple on Android** (optional — Apple sign-in is web-based here): `sign_in_with_apple` needs a
  return-URL `intent-filter` and an Apple **Service ID** + redirect configured to bounce back to the
  app. Skip unless you need Apple login on Android; iOS is the primary surface.

## Backend — `POST /auth/social`

The app sends:

```jsonc
{
  "provider": "google" | "apple",
  "idToken": "<provider OIDC id token>",
  "authorizationCode": "<apple only, single-use>",   // optional
  "email": "…",                                        // optional (first Apple auth only)
  "fullName": "…",                                      // optional (first Apple auth only)
  "country": "QA"
}
```

Backend must: **verify `idToken`** against the provider (Google: audience = server client id;
Apple: Apple public keys + your Service/App id), find-or-create the user, upsert `email`/`fullName`
when present, and return the same `AuthSession` shape as `/auth/otp/verify`
(`{ accessToken, refreshToken, user }`). (Not yet in `openapi.v2.yaml` — add it there.)

## Testing the live flow

```bash
flutter run --dart-define=LIVE=true \
  --dart-define=API_BASE_URL=https://<host>/v1 \
  --dart-define=GOOGLE_SERVER_CLIENT_ID=<web-client-id>.apps.googleusercontent.com
```

With `LIVE` unset the app keeps using `MockSocialAuthService`, so the buttons work offline for demos.

---

<a id="part-4--background--push-setup"></a>
# Part 4 — Background / Push Setup


How the rider app keeps working while backgrounded, and what's left to finish. Three layers:

| # | Capability | Status |
|---|---|---|
| 1 | **Resume cleanly on reopen** — drop socket when idle, reconnect + resync on resume | ✅ **Done** |
| 2 | **Push notifications** when backgrounded/closed (FCM + APNs) | ✅ **Wired** (Firebase project `fleet-bfb36`); iOS needs 3 Xcode/portal steps |
| 3 | **Live trip updates** during an active ride (Android foreground service / iOS location) | 🟡 Native config in place; needs plugin + service |

> **Package id:** the app was renamed to **`com.codepac.fleetapp`** (Android `applicationId` + iOS
> bundle id) to match the Firebase config. The Apple Sign-In App ID must be re-registered for this id.

---

## 1. Lifecycle — done ✅

- `FleetRideApp` is a `WidgetsBindingObserver` (`lib/app.dart`). On `resumed` it calls
  `TripRepository.onAppResumed()` (reconnect + the socket re-subscribes; UI re-snapshots via REST);
  on `paused`/`hidden`/`detached` it calls `onAppBackgrounded()`.
- `onAppBackgrounded()` **drops the socket when idle** (battery) but **keeps it during an active ride**
  (`hasActiveTrip`), so live driver location/status keep flowing (backed by layer 3 below).
- Covered by tests in `test/trip_repository_test.dart` (group `background lifecycle`).

No further work needed.

---

## 2. Push notifications (FCM + APNs) — wired ✅

**In the repo now:**
- Plugins: `firebase_core`, `firebase_messaging`, `flutter_local_notifications` (`pubspec.yaml`).
- `PushService` interface + `MockPushService` (offline default) + `FcmPushService`
  (`lib/core/data/push_service.dart`, `fcm_push_service.dart`), selected by `pushServiceProvider`
  (mock while `useMock`, real FCM on live builds).
- `main()` calls `Firebase.initializeApp()` + registers the background handler **on live builds only**.
- The notification-priming screen calls `pushService.init()` → registers the token via `POST /devices`;
  logout calls `deleteToken()` + `unregisterDevice`.
- Android: Google-Services Gradle plugin applied (`settings.gradle.kts`, `app/build.gradle.kts`),
  `google-services.json` in `android/app/`, `POST_NOTIFICATIONS` permission. **Android is complete.**
- iOS: `GoogleService-Info.plist` copied to `ios/Runner/`, `UIBackgroundModes: remote-notification` set.

**iOS — remaining manual steps (need your Apple account / Xcode):**
1. **Add `GoogleService-Info.plist` to the Runner target** in Xcode (drag it into the Runner group,
   tick "Runner" target). Without this it isn't bundled and `Firebase.initializeApp()` fails at runtime.
2. Enable the **Push Notifications** capability on the Runner target, and upload the **APNs auth key**
   to Firebase (Project settings → Cloud Messaging).
3. `cd ios && pod install` (pulls the Firebase pods).

**Backend:** send FCM/APNs for the same events as the socket (`booking.status_changed`,
`dispatch.ride_assigned`, `notification.created`, …), sharing the notification `id` so the foreground
socket event and the push **dedupe** (see `docs/REALTIME_APP_REQUIREMENTS.md` §3.6).

> Security note: `google-services.json` / `GoogleService-Info.plist` now live in their native
> locations. The copies you added under `docs/` can be deleted — they carry API keys and shouldn't
> be shared.

---

## 3. Live trip in the background 🟡

Keep receiving `driver.location` / `booking.status_changed` while the app is backgrounded **during a
ride** (not after — the socket is dropped when idle by layer 1).

**Native config already applied:**
- Android: `FOREGROUND_SERVICE`, `FOREGROUND_SERVICE_LOCATION`, `ACCESS_FINE_LOCATION`,
  `ACCESS_BACKGROUND_LOCATION` (`AndroidManifest.xml`).
- iOS: `UIBackgroundModes: location` + location usage strings (`Info.plist`).

**Remaining:**
1. Add a foreground-service plugin (e.g. `flutter_foreground_task`).
2. **Start** the service when a ride goes active (`TripRepository.hasActiveTrip` flips true /
   `requestRide`), showing a persistent "Trip in progress" notification; **stop** it on a terminal
   status. The service holds the socket connection so `onAppBackgrounded()` keeps it alive.
3. iOS: the `location` background mode keeps the app running during the trip; start/stop location
   updates alongside the ride so the OS sustains the process. Request "Always" location only when a
   ride needs it.

> Battery note: only run the service **during an active ride**. Layer 1 already guarantees the socket
> is dropped when idle, so there's no always-on drain.

---

## Summary

Layer 1 works today and is tested. Layers 2 & 3 have their **native config wired**; completing them
needs a Firebase project + two plugins (push) and one plugin + a small service (live trip). Both
follow the app's mock-first pattern — add the interface, keep a mock default, drop in the real
implementation behind the same provider.
