# Fleet Ride (Rider App) — Realtime Requirements

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
(client phases; wire status = gateway `booking.status_changed` — see §4 for the enum conflict.)
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

### 3.2 Live fare / meter — **STILL OPEN ⚠️ (two backend docs disagree)**

The rider's on-trip screen shows a **live running fare + elapsed time + distance** for metered
services (Ride / Premium). The app model expects a per-tick update (`MeterReading { time,
distanceKm, total }`).

**Conflict to resolve.** The realtime gateway guide (`docs/realtime (1).md`) event catalog lists
**no meter event** at all. The DriverX API contract (`docs/specification/`) *does* define one —
`meter:tick { booking_id, total_minor, distance_m, duration_s }`. So the meter exists on the driver
side but is **not yet published by the gateway** the rider consumes.

**Ask (Q-2 — still needed):** have the gateway emit the meter to the rider's `booking.{id}` room as
**`booking.meter`** `{ booking_id, elapsed_s, distance_m, running_fare, currency }` (option A, mirror
of the driver `meter:tick`). The rider already binds `booking.meter` but treats it as **proposed**
(`RtEvent.bookingMeter`) until the gateway catalog confirms it.
- (B) derive client-side from `driver.location` ticks + a tariff — fallback if the server won't emit.
- (C) final fare only at `completed` — degrades UX; last resort.

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

## 4. Status enum alignment — **CONFLICT ⚠️ (the two backend docs disagree)**

`booking.status_changed.status` drives our whole FSM, so we need **one** authoritative enum. The two
backend documents currently give **different** vocabularies:

| Source | Status values |
|---|---|
| Realtime gateway — `docs/realtime (1).md` (what the rider socket receives) | `matching · assigned · arriving · arrived · on_trip · completed · cancelled` |
| DriverX API — `docs/specification/` (the `ride_bookings.status` column) | `matching · offered · assigned · arrived · in_progress · completed · cancelled · no_show · scheduled` |

The clashes: **`arriving`** (gateway emits it on navigate-pickup; the DB contract has no en-route
status) and **`on_trip` vs `in_progress`** (same state, two names). The DB contract also adds
`offered`, `no_show`, `scheduled`.

**What the rider does today:** since the gateway (`realtime (1).md`) is what actually reaches the
rider socket, we treat **its** values as primary but accept **both** spellings in
`trip_repository._driveStatus`, so the FSM never stalls whichever the gateway emits:

| Wire `status` | Rider client phase | Notes |
|---|---|---|
| `matching` (+ `offered`) | `assigning` | right after `POST /bookings` |
| `assigned` | `accepted` | fires with `dispatch.ride_assigned` |
| `arriving` | `arriving` | gateway emits on navigate-pickup; also derivable from the first `driver.location` tick if missed |
| `arrived` | `arrived` | driver at pickup |
| `on_trip` **or** `in_progress` | `onTrip` | same state, both accepted |
| `completed` | `completed` | terminal → we unsubscribe |
| `cancelled` (+ `no_show`) | `cancelled` | need `source` + `reason` |
| `scheduled` | `scheduled` | booked-ahead |

**Open Q-3 (now a reconciliation ask, not a guess):** please make `realtime (1).md` and
`docs/specification/` agree on **one** enum and confirm: (a) `arriving` — kept or dropped? (b)
`on_trip` **or** `in_progress` (pick one string); (c) are `offered` / `no_show` / `scheduled` emitted
on `booking.status_changed` to the rider, or driver-only? Plus, on `cancelled`, include **`source`**
(`rider` | `driver` | `office` | `system`) and a human-readable **`reason`**. (`officeConfirmed` /
`assigning` stay **client-only** sub-states — never on the wire.)

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
srv → booking.meter…  (Q-2 — proposed, not yet in gateway catalog) → live fare ticks
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
| Q-2 | **Live meter** — gateway catalog (`realtime (1).md`) lists none; driver spec has `meter:tick`. Emit `booking.meter` to the rider room (§3.2) | **High (blocks on-trip UI)** |
| Q-3 | **Reconcile the status enum** across `realtime (1).md` (`arriving`/`on_trip`) vs `docs/specification/` (`in_progress`/+`offered`/`no_show`/`scheduled`); + `source`/`reason` on cancel (§4) | **High** |
| Q-4 | Driver + vehicle + ETA: inline in `ride_assigned` or via `GET /bookings/{id}` (§5) | **High** |
| Q-5 | Scheduled ride: when to subscribe + is there a "matching started" event (§4 D) | Med |
| Q-6 | Emit `notification.created` on `user.{id}` (spec in §3.6), sharing `id` with FCM — or confirm FCM-only | Med |
| Q-7 | Rider membership/subscription events on `user.{id}`? (§3.5) | Low |
| — | Commit to additive-only changes + a version/changelog signal (§3.5) | Med |
| — | Persist `pickup_note` on the ride-request write path (column already exists) (§3.7) | Low |
| — | Documented payloads for `booking.chat_message`, `chat.message_created`, `support.message_created`, `wallet.credited`, `payment.succeeded`, `rating.received` (§3.3–3.4) | **High** |
| — | `driver.location`: add `heading` + `eta_seconds` (§6) | Med |
| — | Confirm the socket **also** emits `booking.status_changed` on `user.{id}` (not just `booking.{id}`) so we can react before joining the booking channel | Med |

The rider socket layer is **validated** against the realtime gateway contract (`docs/realtime (1).md`):
event names, rooms (`user.{id}`/`booking.{id}`), handshake (`{token, country}`), and the
subscribe/ack + receive-only model all match. The open items are the two **cross-doc conflicts**
above — Q-2 (meter absent from the gateway catalog) and Q-3 (status enum differs between the gateway
guide and the DriverX API contract) — plus Q-4 and the undocumented payloads. Cross-app details in
`docs/DRIVER_APP_BUILD_PLAN.md` §7 and `docs/specification/CROSS_APP_RECONCILIATION.md`.
