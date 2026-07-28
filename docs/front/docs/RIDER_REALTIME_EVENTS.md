# Fleet Ride (Rider) — Realtime Event Contract

**Audience:** the **backend** developer (gateway + outbox) and the **DriverX** app team.
**Purpose:** the complete, single-page list of every realtime event the **rider** app needs —
channel, direction, trigger, exact payload, and what the app does — plus the REST actions that
cause them and the two items still needing a backend ruling.

**Source of truth:** the realtime gateway guide **`docs/realtime (1).md`** (Socket.IO,
`"{channel}:{eventType}"`, receive-only + REST actions). This file is consistent with it and with the
rider code (`lib/core/data/socket_service.dart`, `trip_repository.dart`). Where the DriverX API
contract (`docs/specification/`) disagrees, the gateway guide wins for the wire; the deltas are
called out in §6–7.

Legend: **✅ confirmed** = in the gateway catalog today · **🟡 proposed** = rider needs it, not yet in
the catalog · **⚠️** = needs a backend decision.

---

## 1. Connection (handshake)

- **URL:** `wss://<host>:<FLEET_RT_PORT>` (default `6002`) · Socket.IO client `^4`.
- **Auth handshake:** `{ token, country }` — `token` = Passport access token from
  `POST /api/v1/auth/otp/verify`; `country` = ISO-2 (same as the `X-Country` REST header).
- Missing/invalid token → server emits `unauthorized` and disconnects → re-login/refresh.
- **Receive-only for domain events.** The rider never emits domain events; the only socket messages
  it sends are `subscribe` / `unsubscribe`. All actions are REST (§5).

```js
const socket = io('wss://rt.fleetos.app:6002', {
  transports: ['websocket'],
  auth: { token: accessToken, country: 'QA' },
});
```

## 2. Channels the rider subscribes to

| Channel | When to join | When to leave |
|---|---|---|
| `user.{userId}` | Whole session (account-level events) | On logout |
| `booking.{bookingId}` | The moment a ride becomes **active** (after `POST /bookings`, or a snapshot shows an active ride) | On any **terminal** `booking.status_changed` (`completed`/`cancelled`) **and** after `ride.released` |

Subscribing is **acknowledged** — only join on `{ authorized: true }`. Authorization is server-side:
a rider may join only its own `user.{id}` and a `booking.{id}` it is a party to.
**Re-subscribe on every (re)connect** (the server forgets rooms) and **snapshot via REST** afterward
— Redis pub/sub does not replay missed events.

---

## 3. Events the rider RECEIVES

Listener name is `"{channel}:{eventType}"`. Treat every payload field as optional and re-fetch REST
for authoritative state.

### 3.1 On `booking.{id}` (active ride)

| eventType | Status | Payload | Rider does |
|---|---|---|---|
| `booking.status_changed` | ✅ | `{ booking_id, status, office_id, source, reason?, eta_minutes?, final_fare? }` | Drive the trip FSM (see §6). On terminal status → unsubscribe. |
| `dispatch.ride_assigned` | ✅ | `{ booking_id, driver_id, office_id }` **(+ driver/vehicle — see Q-4)** | Show the assigned driver card. Fetch `GET /bookings/{id}` for driver+vehicle unless inlined. |
| `driver.location` | ✅ | `{ driver_id, lat, lng, heading?, eta_seconds? }` | Move the map pin; update pickup ETA. Also advances `accepted→arriving` if the `arriving` status was missed. |
| `booking.chat_message` | ✅ | `{ id, body, from_type: 'rider'\|'driver', created_at, read_at? }` | Append to the in-trip chat thread. |
| `booking.meter` | 🟡 **Q-2** | `{ booking_id, elapsed_s, distance_m, running_fare, currency }` | Update the live meter (elapsed / distance / running fare) during `on_trip`. |
| `ride.released` | ✅ | `{ booking_id }` | Fare settled → show receipt (`GET /bookings/{id}/receipt`); unsubscribe the channel. |

### 3.2 On `user.{id}` (account-level, whole session)

| eventType | Status | Payload | Rider does |
|---|---|---|---|
| `booking.status_changed` | ✅ | *(as above — mirrored on `user.{id}`)* | React before the booking channel is joined. |
| `dispatch.ride_assigned` | ✅ | *(as above — mirrored)* | Same as booking channel. |
| `notification.created` | 🟡 **Q-6** | `{ id, type, template_key?, title, body, data?, read_at, unread_count }` | Push into the notification center; update the unread badge. |
| `chat.message_created` | ✅ | `{ conversation_id, … }` | New rider↔office message → re-fetch the thread. |
| `support.message_created` | ✅ | `{ ticket_id, … }` | New support-ticket reply → re-fetch the ticket. |
| `wallet.credited` | ✅ | `{ … }` | Refresh wallet balance. |
| `payment.succeeded` | ✅ | `{ … }` | Confirm a payment intent; refresh. |
| `rating.received` | ✅ | `{ … }` | The rider was rated (dual rating). |

> The account-level events (`chat.message_created`, `support.message_created`, `wallet.credited`,
> `payment.succeeded`, `rating.received`) are consumed as **signals** — the rider just re-fetches the
> authoritative REST resource, so their body fields aren't bound field-by-field. Documented payloads
> would still help (§7).

---

## 4. Full event index (copy-paste)

**Rider RECEIVES — `booking.{id}`:** `booking.status_changed`, `dispatch.ride_assigned`,
`driver.location`, `booking.chat_message`, `booking.meter` 🟡, `ride.released`
**Rider RECEIVES — `user.{id}`:** `booking.status_changed`, `dispatch.ride_assigned`,
`notification.created` 🟡, `chat.message_created`, `support.message_created`, `wallet.credited`,
`payment.succeeded`, `rating.received`
**Rider SENDS (socket):** `subscribe`, `unsubscribe` — nothing else.

---

## 5. Rider actions → REST → event received

The rider causes events by calling REST; the gateway pushes the result back. (The app's mock
simulates these; production is REST.)

| Rider action | REST call | Then receives |
|---|---|---|
| Request a ride (with `pickup_note`) | `POST /api/v1/bookings` | `booking.status_changed(matching)`; drivers get `dispatch.offer_created` |
| Schedule a ride | `POST /api/v1/bookings` (scheduled) | `booking.status_changed(scheduled)` then `(matching)` at the pickup window |
| Cancel a ride | `POST /api/v1/bookings/{id}/cancel` | `booking.status_changed(cancelled, source=rider, reason)` |
| Send in-trip message | `POST /api/v1/trips/{id}/messages` | `booking.chat_message` |
| Rate the driver | `POST /api/v1/trips/{id}/rating` | driver gets `rating.received` |
| Register push token | `POST /api/v1/devices` | *(FCM/APNs delivery; no socket event)* |

**`pickup_note`** (free-text hint for the driver) rides on the `POST /bookings` body and must persist
on `ride_bookings.pickup_note`; surface it to the driver on the offer/trip payload.

---

## 6. Trip status enum — ⚠️ ONE conflict to resolve

`booking.status_changed.status` drives the whole rider FSM. The two backend docs currently disagree:

| Source | Values |
|---|---|
| Gateway — `docs/realtime (1).md` (what the rider receives) | `matching · assigned · arriving · arrived · on_trip · completed · cancelled` |
| DriverX API — `docs/specification/` (`ride_bookings.status` column) | `matching · offered · assigned · arrived · in_progress · completed · cancelled · no_show · scheduled` |

Clashes: **`arriving`** (gateway emits on navigate-pickup; DB contract has none) and **`on_trip` vs
`in_progress`** (same state, two spellings). The rider **accepts both spellings** so it never stalls,
but the backend must publish **one** enum.

Rider phase mapping (whichever is chosen):

```
matching/offered → assigning   assigned → accepted   arriving → arriving   arrived → arrived
on_trip/in_progress → onTrip    completed → completed    cancelled/no_show → cancelled
scheduled → scheduled
```

**Ask:** on `cancelled`, always include **`source`** (`rider|driver|office|system`) and a
human-readable **`reason`** (shown to the rider). `officeConfirmed`/`assigning` are **client-only**
sub-states — never put them on the wire.

---

## 7. Open items for the backend

| # | Item | Priority |
|---|---|---|
| **Q-2** | **Live meter** — emit `booking.meter` `{ booking_id, elapsed_s, distance_m, running_fare, currency }` on `booking.{id}` during the trip. Absent from the gateway catalog; DriverX defines `meter:tick`. Mirror one event to both rooms. | **High** (blocks on-trip UI) |
| **Q-3** | **Reconcile the status enum** across `realtime (1).md` and `docs/specification/` (§6) + add `source`/`reason` on cancel. | **High** |
| **Q-4** | **`dispatch.ride_assigned` detail** — inline `driver { name, rating }` + `vehicle { model, plate, colour, class_label }` + `eta_minutes`, or confirm the rider fetches `GET /bookings/{id}`. | **High** |
| **Q-6** | **`notification.created`** on `user.{id}` (payload above), sharing `id` with the FCM push so the center and the push dedupe — or confirm FCM-only. | Med |
| — | **`driver.location` enrichment** — add `heading` (0–359°, pin rotation) and `eta_seconds`. | Med |
| — | **Document payloads** for the account-level events in §3.2 (even though they're re-fetch signals). | Med |
| — | Confirm `booking.status_changed` is emitted on **`user.{id}`** too (not only `booking.{id}`). | Med |

---

## 8. Cross-app pairing (for the DriverX team)

Both apps share the gateway, so the rider's events pair with the driver's on the **same** dotted
scheme + rooms. The driver app should use these names (not the colon scheme in earlier drafts):

| Domain | Rider receives (`user`/`booking`) | Driver receives (`driver`/`booking`) | Backend source |
|---|---|---|---|
| Ride status | `booking.status_changed` | `booking.status_changed` | outbox on status write |
| Assignment | `dispatch.ride_assigned` | `dispatch.ride_assigned` | offer accept |
| New offer | — | `dispatch.offer_created` / `dispatch.offer_expired` | dispatch |
| Driver GPS | `driver.location` (receives) | driver **pushes** via `POST /driver/trips/{id}/location` | relayed to rider |
| In-trip chat | `booking.chat_message` | `booking.chat_message` | `POST /bookings/{id}/chat` |
| Live meter | `booking.meter` 🟡 | `meter:tick` (driver spec) 🟡 | **unify to one event** (Q-2) |
| Settlement | `ride.released` | `ride.released` | trip end |
| Presence | — | `presence.changed` | driver presence |
| Earnings | `wallet.credited` | `wallet.credited` / `wallet.payout` | wallet write |
| Rating | `rating.received` | `rating.received` | dual rating |

**Shared handshake/rooms:** both apps use `auth: { token, country }`; rooms are `user.{id}`,
`driver.{id}`, `booking.{id}` (dotted). Both join `booking.{id}` for the same ride — that is what
makes the shared `booking.status_changed` / `driver.location` / `booking.meter` events work.
**Driver actions are REST too** (availability, presence, navigate/arrive/start/end, chat) — the socket
only echoes the result; see `docs/realtime (1).md` §9b for the driver REST→event table.

---

### One-line asks
1. ☐ Emit `booking.meter` to the rider room (Q-2).
2. ☐ Publish one status enum, reconciling `realtime (1).md` ↔ `docs/specification/` (Q-3).
3. ☐ Inline driver+vehicle in `dispatch.ride_assigned`, or confirm REST fetch (Q-4).
4. ☐ Emit `notification.created` on `user.{id}` sharing the FCM id (Q-6).
5. ☐ Add `heading` + `eta_seconds` to `driver.location`.
6. ☐ Document the account-level event payloads (§3.2).
