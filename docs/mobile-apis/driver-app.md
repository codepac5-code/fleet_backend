# DriverX — Driver App API & Events

Complete request/response + realtime-events contract for the **DriverX** driver app.

- Guard: `auth:driver` (Passport bearer). All paths are under `/api/v1`.
- See [README](README.md) for base URL, headers (`Authorization`, `X-Country`, `Accept-Language`, `Idempotency-Key`), the `{data,meta}` / `{error}` envelope, and the realtime socket transport.
- Money is always **minor units** (integer, e.g. `4200` = 42.00) with a `currency_code`.
- Every endpoint is ✅ built & tested unless marked 🟡.

Error shape (all endpoints): `{ "error": { "code": "<slug>", "message": "..." } }` with the HTTP status noted per case.

---

## 1 · Authentication (OTP + application)

Onboarding order: `apply` (or link office) → office/admin approves in the panel (activates the driver) → `otp/request` → `otp/verify` → token.

### 1.1 Request OTP — `POST /api/v1/driver/auth/otp/request` · public
```json
Request:  { "phone": "+97455123456" }
200:      { "data": { "otp_sent": true, "expires_in": 120, "resend_in": 60 } }
```
Errors: `invalid_phone` 422 · `otp_throttled` 429 (resend before 60s).

### 1.2 Verify OTP — `POST /api/v1/driver/auth/otp/verify` · public
```json
Request:  { "phone": "+97455123456", "code": "4721" }
```
Three outcomes (all `200`):
```json
// approved driver → logged in
{ "data": { "is_registered": true, "status": "active", "access_token": "…", "token_type": "Bearer",
            "driver": { "id": 9, "name": "John Smith", "phone_masked": "+97455123••", "office_id": 3, "is_active": true, "rating": 4.9 } } }
// exists but not yet approved
{ "data": { "is_registered": true, "status": "pending", "driver": { … } } }
// no driver account → app shows Apply / Link screens
{ "data": { "is_registered": false, "status": "not_registered" } }
```
Errors: `invalid_phone` 422 · `code_expired` 410 · `invalid_code` 422 (max 5 attempts).

### 1.3 Apply to drive / link office — `POST /api/v1/driver/auth/apply` · public
Creates a pending application the office/admin panel reviews. Sending `office_id` or `invite_code` marks it a **link** request, otherwise an **apply** request.
```json
Request:  { "phone": "+97455123456", "name": "John Smith", "city": "Doha",
            "vehicle_type": "Standard sedan", "license_number": "QA-DR-10284",
            "office_id": 3, "invite_code": "PRL-4098" }
201:      { "data": { "application_id": 41, "status": "pending" } }
```

### 1.4 Me — `GET /api/v1/driver/auth/me` · driver
```json
200: { "data": { "id": 9, "name": "John Smith", "phone_masked": "…", "office_id": 3, "is_active": true, "rating": 4.9 } }
```

### 1.5 Logout — `POST /api/v1/driver/auth/logout` · driver → `204`

---

## 2 · Availability / presence

### 2.1 Heartbeat — `POST /api/v1/driver/presence` · driver
Publishes availability + location; this is how the driver becomes discoverable to dispatch. Send every ~15–30s while online. Emits `presence.changed` on status change.
```json
Request:  { "office_id": 3, "status": "busy", "busy_reason": "break", "lat": 25.2871, "lng": 51.5310 }
200:      { "data": { "driver_id": 9, "status": "busy", "busy_reason": "break", "heartbeat_at": "2026-07-13T…" } }
```
`status`: `online | busy | offline`. `busy_reason` (only when `busy`): `break | fuel | prayer | vehicle | personal | other`. Missing/invalid status or reason → `422 validation_failed`. Stale presence (no recent heartbeat) is excluded from dispatch.

---

## 3 · Ride offers

### 3.1 Accept — `POST /api/v1/driver/offers/{booking}/accept` · driver
Atomic, first-to-accept-wins. On success the driver is assigned and the trip lifecycle begins.
```json
200: { "data": { "booking_id": 5001, "assigned_driver_id": 9, "status": "assigned" } }
```
Errors: `already_assigned` 409 (lost the race) · `offer_expired` 409.

### 3.2 Reject — `POST /api/v1/driver/offers/{booking}/reject` · driver → `200 { "data": { "ok": true } }`

Incoming offers arrive via the realtime `dispatch.offer_created` event on `driver.{id}` (see §13).

---

## 4 · Trip lifecycle (driver-driven state machine)

All under `/api/v1/driver/trips/{booking}` · driver · the driver must be the assigned driver (else `403 ride_not_assigned`). Each transition emits `booking.status_changed` on `booking.{id}` + `user.{id}` so the rider sees live progress. Illegal transition → `409 invalid_transition`.

Linear flow: `assigned → (navigate-pickup) arriving → (arrived) arrived → (start) on_trip → (end) completed`.

### 4.1 Navigate to pickup — `POST …/navigate-pickup` → status `arriving` (rider notified)
### 4.2 Arrived — `POST …/arrived` → status `arrived`
### 4.3 Start trip — `POST …/start` → status `on_trip` (rider onboard)
### 4.4 End trip — `POST …/end`
Settles the fare (3-way digital split or cash→driver dues) and completes the ride.
```json
Request:  { "distance_m": 4800, "duration_s": 720 }   // optional meter readings
200:      { "data": { "booking_id": 900, "status": "completed", "total_minor": 4200, "payment_method": "wallet", … } }
```
### 4.5 Confirm payment — `POST …/payment/confirm`
Cash screen ("confirm only after receiving cash"). Idempotent — settles if not already settled → `completed`.
### 4.6 Cancel (before start only) — `POST …/cancel`
```json
Request:  { "reason": "vehicle_issue" }
200:      { "data": { "booking_id": 901, "status": "cancelled" } }
```
Refunds the rider's escrow + releases the job + emits `dispatch.job_cancelled`. After `on_trip` → `409 not_cancellable`.
### 4.7 Live location — `POST …/location`
```json
Request:  { "lat": 25.201, "lng": 51.512 }
200:      { "data": { "ok": true } }
```
Emits `driver.location` on `booking.{id}` + `user.{id}` (rider watches the car). Send every few seconds while `arriving`/`on_trip`.

Every trip response body carries: `booking_id, status, service, service_class, pricing_style, currency_code, total_minor, payment_method, pickup{lat,lng,title}, dropoff{lat,lng,title}, channel`.

---

## 5 · Home — `GET /api/v1/driver/home` · driver
Map-first home aggregate.
```json
200: { "data": {
  "status": "online",
  "today": { "earnings_minor": 22600, "trips": 7 },
  "wallet_balance_minor": 64000,
  "currency_code": "QAR",
  "active_trip": { "booking_id": 900, "status": "on_trip", "service": "ride", "service_class": "standard",
                   "payment_method": "wallet", "total_minor": 4200, "currency_code": "QAR",
                   "pickup": {…}, "dropoff": {…}, "channel": "booking.900" }   // null when idle
} }
```

---

## 6 · Earnings — `GET /api/v1/driver/earnings?period=today` · driver
`period`: `today | week | month | all` (default `today`). The transparent money contract:
```json
200: { "data": {
  "period": "today", "currency_code": "QAR", "trips": 7,
  "gross_minor": 89000,               // sum of trip totals
  "cash_collected_minor": 21000,      // cash rides total (held by driver)
  "digital_earnings_minor": 68000,    // driver share of wallet/card rides (credited to wallet)
  "fees_minor": 5750,                 // office + fleet commission
  "cash_due_to_office_minor": 5750,   // commission owed on cash rides → dues
  "adjustments_minor": 0,
  "net_expected_payout_minor": 64250, // wallet_balance − dues_balance
  "wallet_balance_minor": 64000,
  "dues_balance_minor": 0,
  "avg_fare_minor": 12714,
  "rating": { "average": 4.91, "count": 172 },
  "performance": {                    // all-time, not period-filtered
    "offers": { "accepted": 46, "rejected": 3, "expired": 1, "offered": 0 },
    "acceptance_rate": 92.0,          // accepted / (accepted+rejected+expired) %
    "completion_rate": 98.0           // completed / accepted %
  }
} }
```

---

## 7 · Ride history & details

### 7.1 History — `GET /api/v1/driver/trips?cursor=&limit=` · driver
Cursor-paginated completed trips.
```json
200: { "data": [ { "booking_id": 910, "from": "Home", "to": "Airport", "service": "ride", "service_class": "standard",
                   "payment_method": "wallet", "pricing_style": "meter", "total_minor": 4200, "earned_minor": 3444,
                   "currency_code": "QAR", "at": "2026-07-13T…" } ],
       "meta": { "next_cursor": "88", "has_more": true } }
```

### 7.2 Detail — `GET /api/v1/driver/trips/{booking}` · driver
```json
200: { "data": { "booking_id": 910, "from": "…", "to": "…", "service": "ride", "service_class": "standard",
                 "pricing_style": "meter", "payment_method": "wallet", "distance_m": 4800, "duration_s": 720,
                 "office_id": 3, "currency_code": "QAR",
                 "fare": { "total_minor": 4200, "fare_minor": 4200, "discount_minor": 0, "earned_minor": 3444, "fees_minor": 756 },
                 "at": "2026-07-13T…" } }
```
Not your trip → `404 not_found`.

---

## 8 · Payouts

### 8.1 Request payout — `POST /api/v1/driver/payouts` · driver
```json
Request:  { "amount_minor": 50000, "currency_code": "QAR" }
201:      { "data": { "id": 12, "amount_minor": 50000, "currency_code": "QAR", "status": "pending" } }
```
Errors: `insufficient_funds` 422 · `validation_failed` 422. The office/admin panel approves/pays it; you receive `wallet.payout` when paid.

### 8.2 List — `GET /api/v1/driver/payouts` · driver → `200 { "data": [ { id, amount_minor, currency_code, status, processed_at } ] }`

---

## 9 · Dues (cash commission owed to office/fleet)

### 9.1 Show — `GET /api/v1/driver/dues?currency_code=QAR` · driver
```json
200: { "data": { "driver_id": 9, "currency_code": "QAR", "outstanding_minor": 5750 } }
```
### 9.2 Settle from wallet — `POST /api/v1/driver/dues/settle` · driver · `Idempotency-Key` required
```json
Request:  { "amount_minor": 5750, "currency_code": "QAR" }   // amount_minor optional → settles all
200:      { "data": { … } }
```
Errors: `no_dues` 422 · `insufficient_funds` 422 · missing key → `422 validation_failed`.

---

## 10 · Rate the rider — `POST /api/v1/driver/bookings/{booking}/rating` · driver
```json
Request:  { "stars": 5, "comment": "great" }
201:      { "data": { "booking_id": 900, "ratee_type": "user", "ratee_id": 7, "stars": 5 } }
```
Not your ride → `422 ride_not_rateable`. Emits `rating.received` on `user.{id}`.

---

## 11 · Safety & SOS

### 11.1 SOS opened (analytics) — `POST /api/v1/driver/safety/events` · driver
```json
Request:  { "booking_id": 500, "lat": 25.1, "lng": 51.2 }   // lat/lng optional
201:      { "data": { "event_id": 1, "kind": "sos_opened", "status": "open", "booking_id": 500, "office_id": 3 } }
```
### 11.2 SOS confirmed (protected, hold-to-trigger) — `POST /api/v1/driver/safety/sos` · driver
```json
Request:  { "booking_id": 500, "lat": 25.1, "lng": 51.2, "hold_ms": 1500 }
201:      { "data": { "event_id": 2, "kind": "sos", "status": "active", "booking_id": 500, "office_id": 3 } }
```
Alerts the office in realtime (`support.message_created` on `office.{id}` + `booking.{id}` + `driver.{id}`). Missing coords → `422 validation_failed`.
### 11.3 Safety report — `POST /api/v1/driver/safety/report` · driver
```json
Request:  { "booking_id": 500, "category": "rider_behavior", "note": "…" }
201:      { "data": { "event_id": 3, "kind": "report", "category": "rider_behavior", … } }
```

---

## 12 · Shared (driver + rider guards)

- `POST /api/v1/devices` — register push token `{ token, platform }`.
- `GET /api/v1/notifications` · `POST /api/v1/notifications/{id}/read`.
- `GET /api/v1/drivers/{driver}/rating` — public rating summary `{ average, count }`.
- Booking chat (masked, per-ride): `GET|POST /api/v1/bookings/{booking}/chat/messages` · `POST …/chat/read`. Open only while assigned+in-trip (`403 chat_unavailable` before assignment, `403 chat_closed` after settlement).

---

## 13 · Realtime events (subscribe)

Transport: authorize per channel via `/realtime/authorize`, then subscribe to socket rooms. The driver app subscribes to **`driver.{driverId}`** always, and to **`booking.{bookingId}`** for the duration of an active trip.

| Event `type` | Channel(s) | When | Payload |
|---|---|---|---|
| `dispatch.offer_created` | `driver.{id}` | new ride offer (countdown) | `booking_id, driver_id, service_class, eta, expires_at, …` |
| `dispatch.offer_expired` | `driver.{id}`, `booking.{id}` | your offer timed out | `booking_id, driver_id` |
| `dispatch.ride_assigned` | `booking`, `driver`, `office` | you won the trip | `booking_id, driver_id, office_id` |
| `booking.status_changed` | `booking`, `user` | every trip transition you drive | `booking_id, status` |
| `driver.location` | `booking`, `user` | you post live location | `booking_id, driver_id, lat, lng` |
| `dispatch.job_cancelled` | `booking`, `user`, `driver` | trip cancelled | `booking_id, cancelled_by, reason` |
| `ride.released` | `booking`, `driver`, `office` | settlement done on end-trip | `booking_id, driver_id, office_id, total_minor, payment_method` |
| `wallet.payout` | `driver` | office paid your payout | `owner_type, owner_id, amount_minor, …` |
| `rating.received` | `driver` | a rider rated you | `booking_id, stars` |
| `support.message_created` | `office`, `booking`, `driver` | your SOS/safety alert routed | `kind, driver_id, booking_id, …` |
| `booking.chat_message` | `booking` | in-ride chat | `message_id, from, body` |
| `presence.changed` | `driver`, `office` | your availability changed | `driver_id, status, office_id` |

Analytics events (client-side telemetry, not realtime): `driver_go_online, driver_set_busy, trip_request_received, trip_accept_slide_complete, trip_cancel_before_start, trip_started, trip_ended, payment_confirmed, sos_sheet_opened, sos_triggered_protected`.

---

## 14 · State machine

Availability: `offline → online ↔ busy`; while assigned the status is **locked** (auto trip state) until `completed`, then choose `online|busy|offline`.

Trip: `assigned → arriving → arrived → on_trip → completed` (linear); `cancel` allowed only before `on_trip`; no manual status change during a trip; SOS available from every state (emergency requires hold/slide).

Pricing: `ride`/`premium` = live meter · `travel` = fixed A-to-Z (from the rider booking). Settlement releases the held escrow (digital) or records cash commission to driver dues (cash).
