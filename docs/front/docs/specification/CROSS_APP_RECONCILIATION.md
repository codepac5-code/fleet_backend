(../../../fleet_driver_app/docs/specification/CROSS_APP_RECONCILIATION.md)# Cross-App Reconciliation — Rider (Fleet Ride) ↔ Driver (DriverX)

**For the backend developer.** The rider app and the driver app were built separately
and their realtime/API conventions **do not currently agree**. Both now share this
`docs/specification/` folder, so this note collects every divergence and, for each,
either records the resolution or asks for **one** backend ruling. The backend gateway
(`event_outbox` → channels) is the single publisher, so it — not the apps — sets the
canonical wire format; each app then subscribes/translates.

**Provenance:** the driver side is authoritative from `openapi.yaml` / `api_contract.json`
(this repo). The rider side is **as reported by the Fleet Ride app's build plan**
(`../DRIVER_APP_BUILD_PLAN.md` §7) — verify it against the actual rider codebase before
locking anything in.

Legend: ✅ resolved by the driver spec · ⚠️ needs a backend ruling.

---

## 1. Resolved by the driver spec (fold back into the rider app)

### ✅ `status` enum — the driver enum is the DB truth
`ride_bookings.status` is a real column; its vocabulary wins for **both** apps:

```
matching · offered · assigned · arrived · in_progress · completed · cancelled · no_show · scheduled
```

The rider app used `arriving` / `on_trip`, which are **not** DB values. **Rider must adopt**
the DB enum:

| Rider (old) | Canonical (DB) |
|---|---|
| `arriving` | `arrived` |
| `on_trip` | `in_progress` |
| *(missing)* | `offered`, `no_show`, `scheduled` |

> App-local sub-states are fine as long as they never hit the wire: the driver app keeps
> `navigatingToPickup` (folds into `assigned`) and `payment` (folds into `in_progress`) as
> **client-only** phases. The rider app should do the same for any UI-only sub-states.

### ✅ Live meter — mirror one event to both audiences
The rider app *requested* a live meter (`booking.meter`); the driver spec already defines it
as `meter:tick { booking_id, total_minor, distance_m, duration_s }` (minor units). **Resolution:**
the backend emits **one** meter event per booking to **both** the driver room and the rider
room — same payload, same cadence. (Only the event *name* is still subject to §2.1 below.)

---

## 2. Decisions the backend must rule on

### ⚠️ 2.1 Realtime event naming — `.` vs `:`
Today the two apps use different schemes for the same domain events:

| Domain event | Rider name (dot) | Driver name (colon) |
|---|---|---|
| Booking status changed | `booking.status_changed` | `trip:status` |
| Live fare meter | `booking.meter` | `meter:tick` |
| Driver GPS position | `driver.location` (rider receives) | `driver:presence` (driver emits) |
| New offer to driver | `dispatch.offer_created` | `trip:request` |

These are **incompatible on the wire**. Pick **one** canonical scheme for the gateway.

**Recommendation — a single dotted `resource.event` namespace** (`booking.*`, `driver.*`,
`chat.*`, `office.*`). Rationale: it scales cleanly across many event types, the rider app
already uses it, and the driver app's socket layer is a thin adapter (`socket_service.dart`
maps event names → typed `SocketEvent`s), so re-mapping names there is a **localized change**
— no FSM or UI churn. Proposed canonical set:

| Canonical (proposed) | Replaces rider | Replaces driver | Dir |
|---|---|---|---|
| `driver.availability` | — | `driver:availability` | c→s |
| `driver.presence` | (relayed as `driver.location`) | `driver:presence` | c→s |
| `trip.action` | — | `trip:action` | c→s |
| `chat.send` / `chat.read` / `chat.typing` | — | `chat:send`/`chat:read`/`chat:typing` | c→s |
| `booking.offered` | `dispatch.offer_created` | `trip:request` | s→c |
| `booking.ack` | — | `trip:ack` | s→c |
| `booking.location_tick` | — | `location:tick` | s→c |
| `booking.meter` | `booking.meter` | `meter:tick` | s→c |
| `booking.status_changed` | `booking.status_changed` | `trip:status` | s→c |
| `driver.location` | `driver.location` | (from `driver.presence`, relayed to rider) | s→c |
| `office.message` | — | `office:message` | s→c |
| `chat.message` / `chat.read` | — | `chat:message`/`chat:read` | s→c |

> If the backend prefers to keep the driver's colon scheme instead, that's fine — the ask is
> only to pick **one** and publish it once; both apps then subscribe to the same names. The
> table above doubles as the translation map either direction.

**Decision:** ☐ dotted `resource.event` (recommended) ☐ colon `resource:verb` ☐ other: ______

### ⚠️ 2.2 Room / channel naming
| | Rider | Driver |
|---|---|---|
| Per-user | `user.{id}` | — |
| Per-driver | — | `driver:{driver_id}` |
| Per-booking | `booking.{id}` | `trip:{booking_id}` |

**Recommendation:** align to the naming choice in 2.1 and use the **entity** name `booking`
(the table is `ride_bookings`, not `trips`): `user.{id}`, `driver.{id}`, `booking.{id}`.
Both the rider and driver join `booking.{id}` for the same ride, which is what makes the
shared meter/status events work.

**Decision:** ☐ `user.{id}` / `driver.{id}` / `booking.{id}` (recommended) ☐ other: ______

### ⚠️ 2.3 Handshake auth fields
Rider sends `{ token, country }`; driver sends `{ token, driver_id }`. **Recommendation:** a
single superset with a `role` discriminator so the gateway routes rooms the same way for both:

```json
{ "token": "<accessToken>", "role": "driver | rider", "id": 20498, "country": "QA" }
```

`id` = `drivers.id` or `users.id` per `role`; `country` optional (rider-side today). The gateway
derives the join rooms from `role` + `id`.

**Decision:** ☐ unified `{ token, role, id, country? }` (recommended) ☐ keep separate: ______

### ⚠️ 2.4 Base URL prefix
Rider = `/v1`, Driver = `/driver/v1`. **Recommendation:** explicit per-audience prefixes for
both — `/rider/v1` and `/driver/v1` — so guards/scopes are unambiguous. (Keeping the rider on
bare `/v1` is acceptable if you'd rather not break its existing clients.)

**Decision:** ☐ `/rider/v1` + `/driver/v1` (recommended) ☐ `/v1` (rider) + `/driver/v1` ☐ other: ______

---

## 3. What each app changes once the rulings land

- **Rider (Fleet Ride):** adopt the DB `status` enum (`arriving→arrived`, `on_trip→in_progress`,
  handle `offered/no_show/scheduled`); rename socket events + rooms to the canonical scheme;
  add the handshake `role` field; consume the shared `booking.meter`.
- **Driver (DriverX, this repo):** if the dotted scheme is chosen, update the event-name
  constants in `lib/core/services/socket_service.dart` + `mock_socket_service.dart` (typed
  `SocketEvent` mapping) and room strings — **adapter-only, no FSM/UI change**; adopt the
  unified handshake + room names; add `/driver/v1` prefix in `AppEnv` when going live.
- **Backend:** publish the single canonical event set from `event_outbox`; emit shared
  `booking.status_changed` + `booking.meter` to both the driver and rider `booking.{id}` rooms.

## 4. Unaffected (already consistent)

Auth model (phone-OTP → Bearer), money convention (integer minor units + `currency_code` on
new tables; decimal major on legacy), distance/time units (`distance_m` metres, `duration_s`
seconds), and the REST resource shapes are already aligned across both apps — no reconciliation
needed there.

---

## 5. Still-open schema blockers (independent of the above)

From `README.md` / `../BACKEND_HANDOFF_NOTES.md` — these also touch both apps and remain
unresolved:

- **Ownership (§A):** `saved_places`, `safety_contacts`, `lost_items` are rider-owned (`user_id`)
  but are DriverX features too → add polymorphic ownership / `driver_id`, or guarantee every
  driver has a linked `users` row.
- **Rider summary (§B):** the offer/trip payloads need a derived rider `{ name, rating, verified }`
  block (`ride_bookings` carries only `user_id`).

---

### Summary of asks
1. ☐ Rider adopts the DB `status` enum (resolved direction — just confirm).
2. ☐ One realtime event-naming scheme (2.1).
3. ☐ One room-naming scheme (2.2).
4. ☐ Unified socket handshake (2.3).
5. ☐ Base-URL prefix decision (2.4).
6. ☐ Resolve ownership (§5.A) and rider-summary (§5.B) blockers.
