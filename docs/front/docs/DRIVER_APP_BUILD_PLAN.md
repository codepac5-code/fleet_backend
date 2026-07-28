# DriverX (Driver App) — Build Blueprint

A complete plan to build the **driver** companion to Fleet Ride, reusing this rider app's
architecture. Covers: the authoritative API, what to reuse, the file map, every screen, the
state-machine logic, backend requirements, a phased build order, the open decisions, and the
**cross-app consistency** the two apps must share.

> **AUTHORITATIVE API CONTRACT: `docs/specification/`** — this plan is aligned to it, not to the
> older `realtime (1).md` notes.
> - `openapi.yaml` — OpenAPI 3.0.3, **72 REST endpoints**, 27 schemas, Socket.IO under `x-realtime`.
> - `fleet_driver.postman_collection.json` — runnable, 7 folders, bearer auth, OTP auto-captures token.
> - `API_CONTRACT.md` — human-readable per-screen request/response tables (the source for this plan).
> - `README.md` — the gap report (10 gaps fixed + 4 decisions).

**Stack (same as rider):** Flutter · Riverpod · go_router · Socket.IO · FCM push · Hive · **mock-first**.
- **Base URL:** `https://api.fleetos.example/driver/v1` · **Realtime:** `wss://rt.fleetos.example`
- **Auth:** phone-OTP → Bearer (Laravel `driver` guard). `Authorization: Bearer <token>` on all but `/auth/*`.
- **Money:** integer **minor units** + `currency_code` on new tables; **decimal major** on legacy
  (`wallet_transactions`). Never mix.

**Core difference from the rider app:** the rider *requests & watches*; the driver *goes online*,
receives a **timed offer** (`trip:request` + countdown), *accepts atomically*, then drives the trip
while pushing GPS. So the driver adds an **Availability/Presence** flow and an **Offer→Trip** flow.

---

## 1. Reuse map — lift from the rider app (~55% of core)

| Reuse as-is | Adapt | Build new (driver) |
|---|---|---|
| `core/network/*` (api_client, api_config, api_response, session, health) | `socket_service.dart` — driver event set + rooms (see §6.2) | `features/*` (all driver screens) |
| `core/theme/*`, `core/i18n/*` framework | `providers.dart` — driver repositories | `data/offer_*` + `data/driver_trip_*` FSMs |
| `core/widgets/*` (buttons, cards, sheets, badges, MapView) | `mock_backend.dart` / `mock_socket_server.dart` — driver timeline | `data/presence_controller.dart` |
| `push_service.dart`, `fcm_push_service.dart`, `offline_queue.dart` | `auth_*` — OTP flow reused; add application/onboarding | `data/location_pusher.dart` (geolocator) |
| `app.dart` lifecycle observer, `main.dart` bootstrap | `social_auth.dart` (only if drivers use Google/Apple) | `data/earnings_*`, `data/wallet_*`, `data/safety_*` |

---

## 2. File / folder map

```
driverx_app/lib/
├─ main.dart · app.dart                 # bootstrap + lifecycle (reuse rider)
├─ core/
│  ├─ config/app_env.dart               # LIVE / API_BASE_URL / SOCKET_URL / driver_id (adapt)
│  ├─ network/                          # reuse
│  ├─ router/app_router.dart            # driver routes
│  ├─ theme/ · i18n/ · widgets/         # reuse + OfferCard, AvailabilityDock, EarningsChart, SosButton
│  └─ data/
│     ├─ socket_service.dart            # driver events + rooms (adapt — §6.2)
│     ├─ real_socket_service.dart · push_service.dart · fcm_push_service.dart · offline_queue.dart  # reuse
│     ├─ mock_socket_server.dart        # emit trip:request→location:tick→meter:tick… (rewrite)
│     ├─ mock_backend.dart              # driver endpoints (rewrite)
│     ├─ providers.dart                 # driver providers (adapt)
│     ├─ offer_state/_event/_reducer.dart          # Offer FSM (new)
│     ├─ driver_trip_state/_event/_reducer.dart    # driver trip FSM (new)
│     ├─ presence_controller.dart · location_pusher.dart   # availability + GPS (new)
│     └─ repositories/
│        ├─ auth_repository.dart · onboarding_repository.dart
│        ├─ availability_repository.dart · trips_repository.dart · chat_repository.dart
│        ├─ scheduled_repository.dart · earnings_repository.dart · wallet_repository.dart
│        ├─ notifications_repository.dart · profile_repository.dart · documents_repository.dart
│        ├─ places_repository.dart · support_repository.dart · safety_repository.dart
└─ features/
   ├─ entry/     · home/     · offer/    · trip/     · chat/
   ├─ scheduled/ · earnings/ · wallet/   · notifications/
   └─ account/   · support/  · safety/
```

---

## 3. Screens (mapped to the contract)

### Entry & onboarding (§1)
Splash · **OTP request/verify** (`/auth/otp/*`) · Forgot-password (`/auth/password/reset` — **decision**)
· Register as driver (`POST /drivers/applications`) · Link to office (`POST /offices/link-requests`) ·
Onboarding status poll (`GET /drivers/me/onboarding`) · Permissions priming (`PATCH /drivers/me/permissions`)
· Push register (`POST /devices`, `owner_type:"driver"`).

### Home / Availability (§2)
Availability dock (online/busy/offline toggle → `POST /availability`; `busy_reason ∈
break·fuel·vehicle_check·prayer·personal`) · Readiness gate (`GET /availability/readiness` —
vehicle/office/wallet) · Presence heartbeat (`POST /presence` w/ lat/lng/heading/speed) · Home KPIs
(`GET /drivers/me/summary`) · Demand pill (`GET /zones/demand` — **decision**).

### Offer → Trip (§3 — the signature flow)
**Incoming request** on socket `trip:request { booking, offer, countdownSeconds }` → offer sheet with
countdown from `DispatchOffer.expires_at`. Actions (socket `trip:action` + idempotent REST fallback):
`accept` → `arrive` → `start` → `reach` → `complete`; `reject`/`cancel` branches.
- `POST /trips/{id}/accept` → `assigned`, presence `on_trip`
- `POST /trips/{id}/navigate` · `/arrive` (`arrived`) · `/start` (`in_progress`) · `/reach` (fare) ·
  `/complete` (`completed` + commission + wallet) · `/cancel` (`no_show`/`cancelled`)
- Cancel-impact preview: `GET /trips/{id}/cancel-impact?reason=`
- Live: `location:tick` (ETA/distance) and `meter:tick` (running fare) while `in_progress`.

### Chat (§3) · Records (§4) · Scheduled (§5)
Trip chat (`/trips/{id}/messages` + socket `chat:*`) · History (`GET /trips/history`) · Ride details
(`GET /trips/{id}`) · Found item (`POST /trips/{id}/found-items`) · Rate rider (`POST /trips/{id}/rating`) ·
Scheduled marketplace (`GET /scheduled/offers`, claim/release/reminder) · Upcoming committed.

### Earnings / Wallet / Notifications (§6)
Earnings dashboard (`GET /earnings?range=`) · Wallet (`GET /wallet`, transactions) · Cash out
(`POST /wallet/payouts`) · Payment settings (`PATCH /drivers/me/payment`) · Notifications
(`GET /notifications`, mark-read).

### Account / Support / Safety (§7)
Profile & vehicle (`/drivers/me`, `/drivers/me/vehicle`) · Documents (`/drivers/me/documents`) ·
Preferences/locale · Saved places · Trip issues / tickets / replies · Help articles · **Safety**:
contacts, **SOS** (`POST /safety/sos`), live status links, end emergency.

---

## 4. State machines (correct enums from the contract)

**`ride_bookings.status`:** `matching · offered · assigned · arrived · in_progress · completed ·
cancelled · no_show · scheduled`. The app's `navigatingToPickup` and `payment` are **client-only**
sub-states that fold into `assigned` / `in_progress` on the wire.

**Driver Trip FSM:** `offered → assigned → (navigatingToPickup) → arrived → in_progress →
completed`, terminal branches `cancelled` / `no_show`. Pure reducer, illegal transitions rejected
(mirror the rider's `trip_reducer.dart`).

**Presence FSM:** `offline → online → busy(reason) → online`, and `on_trip` is **server-set** on
assignment (never emitted by the client), cleared to `online` on complete.

**Offer FSM:** `idle → offered(countdown) → accepting → won | lost(409) | expired | declined`. Only
`won` (confirmed by the assignment) enters the trip. **Acceptance is atomic — treat the tap as
provisional.**

**Location pusher:** ~5 s GPS loop (`geolocator`) → `driver:presence` socket + `POST /presence`, and
`POST /trips/{id}/location` fallback during a trip. Runs in a **foreground service** (mandatory — live
location is the driver app's core duty).

---

## 5. Data models to build (27 schemas in `openapi.yaml`)

`Driver`, `RideBooking` (driver view), `DispatchOffer`, `DriverPresence`, `CommissionEarning`,
`WalletBalance`, `WalletTransaction` (**decimal major**), `PayoutRequest`, `Vehicle`,
`DriverDocument`, `BookingChatMessage`, `AppNotification`, `RideRating`, `SafetyContact`,
`DriverSafetyEvent`, `SavedPlace`, `Office`, … — full per-column schemas in `openapi.yaml` `#/schemas`.

---

## 6. Backend requirements

Authoritative = **`docs/specification/openapi.yaml`** (72 endpoints, 7 groups: auth 13 · availability 4
· trips 14 · chat 3 · scheduled 5 · earnings/wallet/notifications 8 · profile/settings/places/support/
safety 25). Full request/response tables in `API_CONTRACT.md`.

### 6.1 Ten gaps to implement (from `README.md`, tagged `[GAP]`)
`GET /trips/{id}/rider-contact` · `POST /drivers/me/deletion-requests` · `PATCH /places/{id}` ·
`GET /places/search` · `GET /help/articles/{id}` · `POST /support/issues/{id}/replies` ·
`PATCH & DELETE /safety/contacts/{id}` · `DELETE /safety/status-links/{id}` · `GET /drivers/me/summary`.

### 6.2 Socket.IO (backed by `event_outbox`)
**Connect:** `wss://rt.fleetos.example`, handshake `auth: { token, driver_id }`. **Rooms:**
`driver:{driver_id}`, `trip:{booking_id}` (active trip).
- **Client → Server (6):** `driver:availability`, `driver:presence`, `trip:action`, `chat:send`,
  `chat:read`, `chat:typing`.
- **Server → Client (8):** `trip:request`, `trip:ack`, `location:tick`, `meter:tick`, `trip:status`,
  `office:message`, `chat:message`, `chat:read`.

### 6.3 Hard schema blockers (from the gap report — need backend resolution)
- **§A Ownership:** `saved_places`, `safety_contacts`, `lost_items` are **rider-owned** (`user_id`) but
  are DriverX features → add polymorphic ownership / `driver_id`, or guarantee every driver has a
  linked `users` row.
- **§B Rider summary:** the offer/trip payloads need a rider `{ name, rating, verified }` block derived
  server-side (`ride_bookings` has only `user_id`).

### 6.4 Four decisions to confirm
`POST /auth/login` (keep password login or OTP-only?) · `POST /auth/password/reset` (real set-password
or OTP re-login?) · `GET /zones/demand` (REST / `zone:demand` socket / drop for v1?) · OTP resend
rate-limit policy.

---

## 7. Cross-app consistency — user ↔ driver (READ THIS)

The rider app (this repo) and this driver spec were authored separately and **do not currently agree**.
For a coherent two-app system the backend must pick **one** convention. Flag these to the backend:

| Area | Rider app (built) | Driver spec (`specification/`) | Action needed |
|---|---|---|---|
| **Realtime event names** | `{channel}:{eventType}` — `dispatch.offer_created`, `booking.status_changed`, `driver.location`, `booking.meter` | colon verbs — `trip:request`, `trip:status`, `location:tick`, `meter:tick`, `trip:action` | **Confirm ONE gateway convention** (or a documented translation layer). Today they're incompatible. |
| **Channels / rooms** | `user.{id}`, `booking.{id}` | `driver:{id}`, `trip:{booking_id}` | Align room naming (`.` vs `:`, `booking` vs `trip`). |
| **Handshake auth** | `{ token, country }` | `{ token, driver_id }` | Confirm handshake fields for both. |
| **`status` enum** | rider expected `arriving` / `on_trip` | authoritative DB enum: `arrived` / `in_progress` (+ `offered`, `no_show`, `scheduled`) | **The driver enum is the DB truth — the rider app must adopt it** (update rider `trip_reducer` mapping: `arriving→arrived`, `on_trip→in_progress`). |
| **Base URL** | `/v1` | `/driver/v1` | Confirm per-audience prefixes. |
| **Live meter (rider Q-2)** | requested `booking.meter` | driver has `meter:tick { total_minor, distance_m, duration_s }` | **Answered** — mirror the same meter to the rider channel. |

> Net: this driver spec **resolves rider open questions Q-2 (meter) and Q-3 (status enum)** — fold those
> back into `REALTIME_APP_REQUIREMENTS.md`. The remaining blocker is the **realtime naming/room
> convention**, which the two apps must share.

---

## 8. Build order (phased, mock-first)

1. **Scaffold** — new project; copy reusable core; stub `mock_backend`/`mock_socket_server`. App launches on the mock.
2. **Auth + onboarding** — OTP, application/office-link, permissions, session, router + shell.
3. **Home + availability** — dock toggle, readiness, presence heartbeat, `drivers/me/summary`.
4. **Offer → Trip** — `trip:request` sheet + countdown + atomic accept; the 6 trip actions + FSM;
   `location_pusher` + foreground service; chat. **Milestone that proves the app.**
5. **Records + scheduled** — history, ride details, found-item, rate rider; scheduled marketplace.
6. **Earnings + wallet + notifications** — dashboards + payout request.
7. **Account + support + safety** — profile, vehicle, documents, places, tickets, **SOS**.
8. **Native + live** — FCM (offer push), foreground GPS, maps; then `LIVE=true` against the real API.

Each phase stays green on the mock, then swaps to the real backend by flipping `useMock`.

---

## 9. References
- **Driver API (authoritative):** `docs/specification/{openapi.yaml, API_CONTRACT.md, fleet_driver.postman_collection.json, README.md}`
- Rider realtime + cross-app: `docs/REALTIME_APP_REQUIREMENTS.md`, `docs/BACKEND_HANDOFF.md`
- Native/push patterns to copy: `docs/BACKGROUND_SETUP.md`, `docs/SOCIAL_AUTH_SETUP.md`
- DB models: `docs/Models/*.php`, `docs/migrations/*driver*`, `*dispatch*`, `*payout*`, `*presence*`
