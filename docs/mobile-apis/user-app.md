# Fleet Ride — Rider App API & Events

Guard: `auth:user` (Passport). All paths are under `/api/v1`. See [README](README.md) for base URL, headers, envelope, money, pagination, and the realtime transport.

This document is **screen-driven**: it follows the Fleet Ride UI/UX board (v9, 27 screens + 9 system states). Each screen lists the exact endpoints and events it consumes.

Legend: ✅ built & tested on the v1 core · 🟡 to build (design below) · ⚙️ backend logic exists, HTTP surface to expose.

Money is always integer **minor units** + `currency_code`. Sharded resources (offices, drivers, bookings, wallets) require `X-Country`. Users are global.

---

## Service model (source of truth)

Every screen respects the FleetOS service structure:

- **3 public services**: `ride` (meter) · `premium` (meter) · `travel` (fixed A-to-Z). One service = one pricing style, never both.
- The rider picks the **class**; the backend **detects the route type** (local / airport / intercity / tourism / cross-border) from pickup + drop-off.
- Only classes and offices that can actually fulfil the exact trip are shown.
- `pricing_style`: `meter` returns a `fare_range` (est.) · `fixed` returns a single locked `fare_minor`.

`service` enum: `ride | premium | travel`
`service_class` enum: `standard | comfort | electric | suv | van | luxury | ultra_luxury` (availability depends on service).

---

# Flow A · Entry (screens 01–03)

## 03 · Login / Sign-up + OTP ✅ (built & tested)

Phone-first, OTP, with Apple/Google and guest browsing. Passport issues the bearer token on verify.

> Built on the v1 core (`AuthController` → `OtpAuthService`, `TokenIssuer` seam → Passport). Design notes: phone is normalized to **E.164** and stored in `users.phoneNumber` (lookup key); OTP codes live in cache (TTL 120s, 60s resend throttle, 5-attempt cap); a new user row is created on first verify (`is_new_user:true` → app collects name). SMS delivery is a no-op hook pending the SMS gateway. `POST /auth/social` returns `422 social_unavailable` until Apple/Google keys are wired (`SocialVerifier` seam).

### 3.1 Request OTP
```
POST /api/v1/auth/otp/request
{ "phone": "+97455123456" }
→ 200 { "data": { "otp_sent": true, "expires_in": 120, "resend_in": 24 } }
```
`422 invalid_phone`. Rate-limited per phone/IP.

### 3.2 Verify OTP → session
```
POST /api/v1/auth/otp/verify
{ "phone": "+97455123456", "code": "7341", "device": { "token": "...", "platform": "ios" } }
→ 200 { "data": {
    "access_token": "...", "token_type": "Bearer", "expires_at": "...",
    "is_new_user": false,
    "user": { "id": 7, "name": "...", "phone": "+9745512••", "phone_verified": true, "locale": "en" }
} }
```
`422 invalid_code` · `410 code_expired`. `is_new_user:true` → app routes to a light profile-completion step (name).

### 3.3 Social sign-in
```
POST /api/v1/auth/social
{ "provider": "apple|google", "id_token": "...", "device": { ... } }
→ 200 { "data": { "access_token": "...", "is_new_user": true, "user": { ... } } }
```

### 3.4 Session lifecycle
```
POST /api/v1/auth/logout            → 204   (revokes current token)
GET  /api/v1/auth/me                → 200 { "data": { user profile } }
```
Guest mode = no token; the app may browse the marketplace (quotes, offices, plans) but any mutating/booking call returns `401 unauthorized` → app opens login.

---

# Flow B · Booking (screens 04–07)

## 04 · Home dashboard ✅ (built & tested)

One call powers the home: greeting, the 3 services, saved-place chips, favorite-office quick glance, wallet balance, active promo banner, and any in-progress trip (to resume).

```
GET /api/v1/home
→ 200 { "data": {
    "services": [
      { "key": "ride",    "pricing_style": "meter", "title_key": "service.ride" },
      { "key": "premium", "pricing_style": "meter", "title_key": "service.premium" },
      { "key": "travel",  "pricing_style": "fixed", "title_key": "service.travel" }
    ],
    "saved_places": [ { "id": 1, "label": "home", "title": "...", "lat": .., "lng": .. } ],
    "favorite_offices": [ { "office_id": 3, "name": "...", "logo_url": "...", "rating": 4.8 } ],
    "wallet": { "currency_code": "QAR", "balance_minor": 13250 },
    "active_trip": null,
    "promo": { "code": "QATAR10", "title_key": "...", "discount_label": "10% off first ride" }
} }
```
Composes: FavoriteOffice (✅), FleetWalletService.walletBalanceMinor (✅), the active-trip resolver (🟡, see 10–14), SiteSetting-driven promo banner. `active_trip` non-null → app deep-links straight into the live-trip screen.

## 05 · Destination search ✅ (built & tested)

Autocomplete, reverse-geocode the current pin, saved + recent places. Backed by `GOOGLE_MAPS_KEY` (kept verbatim) server-side so the key never ships in the app. Built behind a `GeocodingProvider` seam (Google impl + Null fallback when no key); autocomplete drops entries with empty `place_id`; `recent` dedups recent drop-offs from the rider's `ride_bookings`.

```
GET  /api/v1/places/autocomplete?q=hamad&lat=25.28&lng=51.53&session=<uuid>
→ 200 { "data": [ { "place_id": "...", "primary": "Hamad International Airport",
                    "secondary": "Ras Abu Aboud, Doha", "kind": "airport" } ] }

GET  /api/v1/places/{place_id}          → 200 { "data": { place_id, title, lat, lng, kind } }
GET  /api/v1/places/reverse?lat=&lng=   → 200 { "data": { title, lat, lng } }
GET  /api/v1/places/recent              → 200 { "data": [ recent drop-offs ] }
```
`kind: airport` lets the app pre-badge "Fixed A-to-Z available".

### Saved places (also used by 22/26) ✅ (built & tested)
```
GET    /api/v1/saved-places                 → [ { id, label, title, lat, lng } ]
POST   /api/v1/saved-places   { label:"home|work|other", title, lat, lng }  → 201
PATCH  /api/v1/saved-places/{id}            → 200
DELETE /api/v1/saved-places/{id}            → 204
```

## 06 · Choose class (with route preview) ✅ (built & tested)

After route + service, return the route geometry and a quote **per fulfillable class**. Meter classes carry a `fare_range`; Travel classes a locked `fare_minor`.

```
POST /api/v1/trip-options
{ "service": "travel", "pickup": { "lat":25.28,"lng":51.53 }, "dropoff": { "lat":25.27,"lng":51.60 } }
→ 200 { "data": {
    "route": { "distance_m": 14800, "duration_s": 1320, "detected_route_type": "airport",
               "polyline": "enc:..." },
    "pricing_style": "fixed",
    "currency_code": "QAR",
    "classes": [
      { "class": "standard", "available": true,  "fare_minor": 5500 },
      { "class": "electric", "available": true,  "fare_minor": 7000, "guarantee": "ev" },
      { "class": "van",      "available": false, "reason": "no_supply" }
    ]
} }
```
For `service: ride|premium`, each class returns `"fare_range_minor": [2200, 2600]` and `pricing_style: "meter"` instead of a single `fare_minor`. Each class fare reuses `PricingService.quote` (✅) per office-tariff resolution; classes with no office offering them return `available:false, reason:"no_supply"`. `service_tariffs` gained a `service` dimension so an office can price the same class under both meter (Ride) and fixed (Travel). Route geometry is straight-line for now (real Google Directions arrives with Places §05, key stays server-side).

## 05b · Availability check (screen state)

Client-side progressive state rendered while `POST /trip-options` resolves; the response's `classes[].available` + `reason` drive the "checking offices / matching drivers" rows. No dedicated endpoint.

## 07 · Office select & request ✅ (marketplace built & tested)

List the offices that can fulfil the exact **service + class + route**, each with its own quote, ETA, rating, trust and the best-match flag. This is FleetOS's moat. Built as `MarketplaceService.officesAvailable`; ranking = has-supply first, then ETA, price, rating. Offices with no online driver are still listed (`eta_min:null`, sorted last) until the stricter supply filter lands. Office identity (name/logo/verified) is a best-effort overlay from the offices table.

```
POST /api/v1/offices/available
{ "service":"travel", "service_class":"standard",
  "pickup":{"lat":25.28,"lng":51.53}, "dropoff":{"lat":25.27,"lng":51.60} }
→ 200 { "data": {
    "best_office_id": 3,
    "offices": [
      { "office_id": 3, "name": "Hala Taxi Office", "logo_url": "...",
        "verified": true, "monitoring": true, "rating": 4.8, "trips_count": 12400,
        "eta_min": 6, "pricing_style": "fixed", "fare_minor": 5500,
        "why": "fastest airport-ready Standard Travel provider",
        "free_cancel_min": 5, "cars_nearby": 8 }
    ]
} }
```
Ranking (best_office_id) blends ETA + price + rating + live supply. Only offices that are online, granted, priced, in-geofence, and hold a matching driver/vehicle appear (empty `offices:[]` → screen S1).

### 07b · Office details (optional drawer)
```
GET /api/v1/offices/{office}?service=travel&class=standard&pickup=..&dropoff=..
→ 200 { "data": { office identity + support_online, free_cancel_min, cars_nearby,
                   price_explanation, rating breakdown } }
```

### Request ride (single CTA — folds hold + dispatch) ✅ (built & tested)
Booking is created, fare is held in escrow, and the dispatch job starts — atomically, idempotently. Built as `RideBookingService.create` over a new `ride_bookings` entity (its `id` is the `booking_id` the dispatch/ledger core keys on). `payment_method` is `wallet` (default) or `cash` (skips the wallet hold) until stored cards (§27) land. `promo_code` currently recognizes `QATAR10` (10%).
```
POST /api/v1/bookings
Idempotency-Key: <uuid>
{ "office_id": 3, "service": "travel", "service_class": "standard",
  "pickup": { "lat":25.28,"lng":51.53, "note":"Gate 2" },
  "dropoff": { "lat":25.27,"lng":51.60 },
  "payment_method_id": 12, "promo_code": "QATAR10" }
→ 201 { "data": {
    "booking_id": 5001, "status": "matching", "office_id": 3,
    "pricing_style": "fixed", "fare_minor": 5500, "discount_minor": 550,
    "total_minor": 4950, "currency_code": "QAR",
    "held_minor": 4950, "channel": "booking.5001" } }
```
Server pipeline: create Booking → `BookingHoldService.hold` (✅ escrow) → `DispatchService.createJob`+`offerWave` (✅). `422 insufficient_funds` (cash bypasses the wallet hold) · `409 office_unavailable` → app falls back to S2 alternatives. The rider subscribes to `booking.{id}` and its `user.{id}` channel immediately.

---

# Flow C · Live trip (screens 09–14)

## Live trip resolver (powers 09–14) ✅ (built & tested)
```
GET /api/v1/bookings/{booking}
→ 200 { "data": {
    "booking_id": 5001, "status": "matching|assigned|arriving|arrived|on_trip|completed|cancelled",
    "office": { office_id, name, logo_url, verified, monitoring, phone_masked },
    "pricing_style":"fixed", "fare_minor":5500, "total_minor":4950,
    "driver": null | { "id":9, "name":"Rashid K.", "rating":4.9,
                       "vehicle":{ "model":"Toyota Camry","color":"White","plate":"348 921","class":"standard" },
                       "lat":.., "lng":.., "eta_min":6 },
    "meter": null | { "running": true, "elapsed_s":1104, "distance_m":12400, "amount_minor":4150 },
    "route": { "polyline":"...", "remaining_m":9100, "eta_at":"16:42" },
    "cancel": { "free_until":"assigned", "fee_minor":500 }
} }
```
`GET /api/v1/bookings/{booking}` is the single source for every live screen; sockets push deltas, this call rehydrates on resume/reconnect (socket delivery is best-effort — see README).

## 09 · Matching ✅ (cancel + change-office built & tested)
Driven by events on `booking.{id}` (`dispatch.offer_created` fan-out is driver-side; the rider sees `booking.status_changed`, then `dispatch.ride_assigned`). Screen actions:
```
POST /api/v1/bookings/{booking}/cancel     → 200 (refunds the escrow hold) · body { reason? }
POST /api/v1/bookings/{booking}/change-office  { office_id }  → 200 (re-quote + re-dispatch; 409 already_assigned once a driver is on)
```
Note: cancel currently refunds the **full** hold; the post-assignment `fee_minor` is surfaced in `GET /bookings/{id}.cancel` but not yet charged. Rich `office`/`driver` blocks in the resolver are enriched when the marketplace read-model (Flow B §20) and driver profile land; today they carry ids + driver live lat/lng.

## 10 · Ride accepted / driver assigned
Arrives via `dispatch.ride_assigned` on `booking.{id}` + `user.{id}`. `GET /bookings/{id}` now returns the `driver` block. Cancel becomes `fee_minor` after assignment.

## 11 · Arriving · 12 · Arrived · 13 · In-ride
Live driver position + status + (for meter services) the running meter arrive as socket deltas on `booking.{id}`:

| Event | Payload | Meaning |
|---|---|---|
| `booking.driver_location` | `{ lat, lng, eta_min }` | animate the car pin |
| `booking.status_changed` | `{ status:"arriving|arrived|on_trip" }` | swap the trip screen |
| `booking.meter_tick` | `{ elapsed_s, distance_m, amount_minor }` | update the live meter (Ride/Premium only) |

Rider actions across 11–13:
```
POST /api/v1/bookings/{booking}/pickup-note   { note }           → 200
POST /api/v1/bookings/{booking}/im-here                          → 200 (pings driver)
POST /api/v1/bookings/{booking}/share         → 200 { "share_url": "https://…/t/<token>" }
```

## 11b · Driver chat (masked, post-acceptance only) ✅ (built & tested)
Rider↔driver in-app messaging — **distinct** from the office chat (Flow E §4). Available only once a driver is assigned; numbers are never exposed. Both sides use the same endpoints (rider under `auth:user`, driver under `auth:driver`); the server resolves the actor from the authenticated guard + ownership. `403 chat_unavailable` before assignment · `403 chat_closed` after completion · `403 forbidden` if not a party.
```
GET  /api/v1/bookings/{booking}/chat/messages?before_id=   → 200 [ { id, from:"rider|driver", body, at } ]
POST /api/v1/bookings/{booking}/chat/messages  { body }    → 201
POST /api/v1/bookings/{booking}/chat/read                  → 200
```
Event `booking.chat_message` on `booking.{id}` → `{ message_id, from, body }`. Quick-replies are client-side canned strings; they post to the same endpoint. `403` before assignment / after completion.

## 14 · Safety & help
Two-layer support surfaced from every trip screen. See Flow E §Support for the endpoints (`support/office/call-info`, `support/tickets`, `safety/report`, `safety/sos`, `bookings/{id}/share`). SOS shares live trip data per Qatar regulations.

---

# Flow D · Post-trip (screens 15–17)

## 15 · Trip completed
Arrives via `ride.released` on `booking.{id}` (settlement done server-side by `RideLifecycleService` ✅). `GET /bookings/{id}` returns the final `fare_breakdown`. Actions: rate (16), receipt (17), report issue (ticket), favorite office (✅), rebook.
```
POST /api/v1/bookings/{booking}/rebook  → 201 { new booking (same office pre-selected) }
```

## 16 · Dual rating ✅ (both directions built & tested)
Two separate reputations. Reuses `RideRatingController.rateDriver` (✅) plus `rateOffice` (✅). The rating core now keys by `(booking_id, rater_type, ratee_type)` so a rider can rate the driver **and** the office on the same booking. `tags`/`book_again` are accepted but not yet persisted (need columns); `favorite:true` is folded into FavoriteOffice today.
```
POST /api/v1/bookings/{booking}/rating          # driver ✅
{ "stars": 5, "tags": ["smooth_driving","clean_vehicle"], "comment": "..." }
→ 201 { "data": { ratee_type:"driver", ratee_id:9, stars:5 } }

POST /api/v1/bookings/{booking}/office-rating    # office 🟡
{ "stars": 4, "tags": ["fast_response","great_support"], "book_again": true, "favorite": true }
→ 201 { "data": { ratee_type:"office", ratee_id:3, stars:4 } }
```
`favorite:true` folds into FavoriteOffice (✅). `tags` are validated against a per-direction catalog. Emits `rating.received` to the ratee.

## 17 · Trip receipt / details ✅ (built & tested)
```
GET /api/v1/bookings/{booking}/receipt
→ 200 { "data": {
    route:{ from, to, at, distance_m, duration_s, polyline },
    office, driver, pricing_style, fare_breakdown:[ {label, amount_minor} ],
    total_minor, payment_method, support_tickets:[ { id, subject, status } ] } }
```
`GET /bookings/{id}/receipt.pdf` returns a signed PDF url. Fare breakdown mirrors the ledger.

---

# Flow E · Marketplace & account (screens 18–23)

## 18 · Trip history ✅ (built & tested)
```
GET /api/v1/bookings?status=upcoming|completed|cancelled&cursor=&limit=20
→ 200 { "data": [ { booking_id, from, to, at, service, office:{name,logo_url},
                    total_minor, rating_state:"rated|unrated", stars? } ],
        "meta": { next_cursor, has_more } }
```
Read model over the tenant Booking table; `rating_state` flags "Rate trip". Each row → rebook / receipt.

## 19 · Favorite offices ✅
```
GET    /api/v1/favorites/offices              → { "data": { "office_ids": [3,9] } }
POST   /api/v1/favorites/offices/{office}     → 201 { office_id, favorite:true }
DELETE /api/v1/favorites/offices/{office}     → { office_id, favorite:false }
```
Card metadata (rating, classes, pricing styles, response time, support hours) comes from `GET /offices/{office}`.

## 20 · Offices marketplace · office profile ✅ (profile + browse/search built)
```
GET /api/v1/offices?search=&service=&cursor=      → [ office summaries ]   ✅
GET /api/v1/offices/{office}   ✅ (MarketplaceService.officeProfile)
→ 200 { "data": { office_id, name, logo_url, verified, since_year,
        stats:{ rating, trips_count, response_seconds, support_hours },
        about, services:[ {service, pricing_style, classes:[] } ],
        cancellation_policy, reviews:[ {stars, comment} ], is_favorite } }
```

## 21 · Wallet / payment ✅ (+ methods 🟡)
```
GET  /api/v1/wallet/balance?currency_code=QAR   → 200 { owner_type:"user", balance_minor:13250 }   ✅
POST /api/v1/wallet/topups                        → 201 { topup intent }                             ✅
  Idempotency-Key + { amount_minor, currency_code, provider:"stripe|syriatel|mtn" }
GET  /api/v1/wallet/transactions?cursor=          → 200 [ ledger read-model rows ]                  ✅
GET  /api/v1/refunds                              → 200 [ { uuid, amount_minor, status, at } ]      ✅
```
Payment keys preserved verbatim (Stripe / MTN / Syriatel). Provider client_secret return (Stripe PaymentIntent) wired per §27.

## 22 · Profile ✅ (built & tested)
```
GET   /api/v1/profile                → 200 { id, name, phone_masked, phone_verified, email, locale, avatar_url }
PATCH /api/v1/profile   { name?, email?, locale? }   → 200
```
`name` writes to the shared user; `email`/`locale` live on a shadow `rider_profiles` table (legacy users table untouched). Avatar uses `POST /profile/avatar` (§26).
Language toggle sets `locale` (`en|ar`) → drives `Accept-Language` + RTL. Saved places (§05), favorite offices (§19), safety contacts (below), notifications, privacy.

### Safety contacts ✅ (built & tested)
```
GET    /api/v1/safety-contacts               → [ { id, name, phone, auto_share } ]
POST   /api/v1/safety-contacts  { name, phone, auto_share:true }   → 201
DELETE /api/v1/safety-contacts/{id}          → 204
```

## 23 · Support center ✅ (two-layer model, built & tested)
```
GET  /api/v1/support/office/call-info?booking_id=   → 200 { office_name, phone_masked, online:true }
POST /api/v1/support/tickets
  { category:"past_trip|lost_item|refund|safety|office_complaint", booking_id?, subject, body }
  → 201 { ticket_id, layer:"office|fleetos", status:"open" }
GET  /api/v1/support/tickets?status=            → [ { ticket_id, category, subject, status, layer, last_reply_at } ]
GET  /api/v1/support/tickets/{id}               → { ticket + messages[] }
POST /api/v1/support/tickets/{id}/messages { body } → 201
```
Routing rule (server-enforced by `SupportLayer::forCategory`): trip-ops categories (past_trip · lost_item · office_complaint · safety_report) → office; refund · payment · safety · policy · sos → FleetOS. Office-layer tickets resolve the office from the owned booking and notify it. Built on a clean rider-support entity (separate from the office↔fleet `SupportService`). `bookings/{booking}/share` returns a stateless signed `share_url`; the public read-only viewer page is served at `GET /t/{id}-{token}` (hmac-verified) ✅.
```
POST /api/v1/safety/report  { booking_id, kind:"driver|vehicle|route", note }  → 201
POST /api/v1/safety/sos     { booking_id, lat, lng }                            → 201  (shares trip data)
```

---

# Flow F · Scheduled & B2B (screens 24–25)

## 24 · Scheduled Travel / Airport ✅ (built & tested)
```
POST /api/v1/scheduled/offers
{ "service":"travel","service_class":"standard","pickup":{..},"dropoff":{..},
  "scheduled_at":"2026-06-16T04:30:00Z","passengers":2,"luggage":3,"flight_no":"QR8412" }
→ 200 { "data": { offers:[ { office_id, name, verified, rating, fare_minor, free_wait_min,
                             perks:["flight_tracking","meet_greet"] } ] } }

POST /api/v1/scheduled/bookings
Idempotency-Key: <uuid>
{ office_id, service, service_class, pickup, dropoff, scheduled_at, passengers, luggage, flight_no, payment_method_id }
→ 201 { booking_id, status:"scheduled", scheduled_at, fare_minor, office }

GET   /api/v1/scheduled/bookings                 → [ scheduled trips ]
GET   /api/v1/scheduled/bookings/{id}            → { assignment timeline: booking_confirmed / office_assigned / driver_pending / driver_enroute }
PATCH /api/v1/scheduled/bookings/{id}            → edit (date/pax/luggage)
POST  /api/v1/scheduled/bookings/{id}/cancel     → 200 (free until 2h before; then fee)
```
Fixed offers lock the A-to-Z fare before confirm. The scheduled booking is created with `status:"scheduled"` (no wallet hold, no dispatch yet); a driver is assigned ~2h before pickup by an activation job (which then holds the fare + dispatches and the trip joins the live path, Flow C). `GET /scheduled/bookings/{id}` returns the assignment `timeline` (booking_confirmed → office_assigned → driver_pending → driver_enroute). Edit only while `scheduled`; free cancellation until 2h before, then a fee.

## 25 · Corporate & Family 🟡 (later — flag)
Corporate billing profile, admin-set preferred office, repeat bookings, monthly invoices; Family trusted-office + ride-for-someone with auto trip-sharing to guardians. Extends the same booking core with a `billing_profile_id` / `ride_for` on `POST /bookings`. Deferred until the core rider flow ships.

---

# Flow G · System states (S1–S7)

All are UI states over the endpoints above — no new endpoints, but the app relies on these contracts:

| State | Signal |
|---|---|
| S1 No office available | `POST /offices/available` → `offices:[]` → offer Schedule / notify |
| S2 Office rejected | `booking.status_changed{status:"rejected"}` → `POST /offices/available` alternatives → re-request; wallet auto-released (not charged) |
| S3 Payment failed | `POST /bookings` → `422 insufficient_funds` / provider decline → retry / wallet / cash; trip unaffected |
| S4 Offline | app caches `office.phone_masked` from `GET /bookings/{id}` for offline calling |
| S5 Location denied | manual pin → `POST /bookings` with explicit `pickup.lat/lng` |
| S6 RTL / Arabic | `Accept-Language: ar` on every call; numerals isolated client-side |
| S7 Empty / toasts | `GET /bookings` empty; refund/ticket states via §21/§23 |

---

# Flow H · Account completion (screens 26–27)

## 26 · Edit profile ✅ (built & tested)
`PATCH /api/v1/profile` (§22) + avatar upload:
```
POST /api/v1/profile/avatar   (multipart: file)   → 200 { avatar_url }
DELETE /api/v1/account        { confirm_phrase }   → 202 (soft-delete, revokes tokens)
```
Phone change re-runs OTP (§03).

## 27 · Payment methods & add card ✅ (built & tested; live gateway 🟡)
```
GET    /api/v1/payment-methods               → [ { id, brand, last4, exp, is_default, type }, { id:"wallet", type:"wallet" }, { id:"cash", type:"cash" } ]
POST   /api/v1/payment-methods/setup-intent  → 201 { client_secret }   (🟡 422 payments_unavailable until the card gateway is wired)
POST   /api/v1/payment-methods               { gateway_token, brand?, last4?, exp? }  → 201 { id, brand, last4 }
PATCH  /api/v1/payment-methods/{id}/default  → 200
DELETE /api/v1/payment-methods/{id}          → 204
POST   /api/v1/promos/apply                  { code }  → 200 { code, discount_label, valid }
```
Cards are tokenized by the licensed gateway; the app never sends raw PANs to Fleet Ride. Built behind a `CardGateway` seam (Null fallback returns `payments_unavailable` for `setup-intent`/auto-describe until the region gateway is wired — Stripe now; Tap / MyFatoorah / PayTabs / Dibsy confirmed at build). Cards can still be stored by passing `brand`/`last4` from the client SDK after tokenization. First card becomes default; removing the default promotes the next.

---

# Additional endpoints (reference)

Endpoints used across screens, with exact request/response. All ✅ built & tested.

## Office chat (rider ↔ office) — `auth:user`
Separate from the per-ride masked driver chat (§13). This is the persistent rider↔office thread.
```json
GET  /api/v1/chat/conversations
  → 200 { "data": [ { "id": 4, "office_id": 3, "booking_id": 5001, "last_message_at": "…" } ] }
POST /api/v1/chat/conversations                     { "office_id": 3, "booking_id": 5001 }
  → 201 { "data": { "id": 4, "office_id": 3, "booking_id": 5001, "last_message_at": null } }
GET  /api/v1/chat/conversations/{conversation}/messages?limit=30&before_id=
  → 200 { "data": [ { "id": 9, "sender_type": "user|office", "sender_id": 7, "body": "…", "read_at": null, "created_at": "…" } ] }
POST /api/v1/chat/conversations/{conversation}/messages   { "body": "hello" }
  → 201 { "data": { "id": 10, "conversation_id": 4, "sender_type": "user", "body": "hello", "created_at": "…" } }
POST /api/v1/chat/conversations/{conversation}/read
  → 200 { "data": { "conversation_id": 4, "marked_read": 2 } }
```
Foreign conversation → `404 not_found`. `body` required → `422 validation_failed`. Office reply arrives as `chat.message_created` on `user.{id}`.

## Notifications — `auth:user,driver`
```json
GET  /api/v1/notifications?cursor=&limit=   → 200 { "data": [ { "id", "type", "title", "body", "read_at", "created_at" } ], "meta": { next_cursor, has_more } }
POST /api/v1/notifications/{id}/read        → 200 { "data": { "id": 12, "read_at": "…" } }
POST /api/v1/devices                        { "token": "fcm-…", "platform": "android|ios" }  → 201 { data:{ owner_type, owner_id } }
```

## Subscription plans (public)
```json
GET /api/v1/subscription-plans   → 200 { "data": [ { "plan_key": "pro", "price_minor": …, "currency_code": "QAR", "fleet_commission_rate": …, "office_commission_rate": …, "is_popular": true } ] }
```

## Booking hold (standalone escrow hold) — `auth:user`
Normally `POST /bookings` holds automatically; this explicit endpoint (re)holds a booking's fare.
```json
POST /api/v1/bookings/{booking}/hold   { "office_id": 3, "service_class": "standard", "distance_m": 3000, "duration_s": 600 }
  → 201 { "data": { … held … } }
```
Errors: `tariff_not_found` 404 · `insufficient_funds` 422 · `office_id` required → 422.

## Booking refund — `auth:user` · `Idempotency-Key` required
```json
POST /api/v1/bookings/{booking}/refund   { "amount_minor": 2400, "currency_code": "QAR", "from_escrow": true }
  → 200 { "data": { "uuid", "kind": "refund", "status", "ledger_transaction_uuid" } }
```
Errors: missing key / non-positive amount → 422. Refund lands in the rider wallet; also visible via `GET /refunds` (§21).

> Server-to-server (not app-facing): `POST /api/v1/payments/webhook/{provider}` (gateway callback) and `POST /api/v1/dispatch/jobs` (internal dispatch trigger).

---

# Realtime events (rider receives)

Connect per README (`io(url,{auth:{token}})`), then subscribe to `user.{id}` and, for each active ride, `booking.{id}`. Events arrive as `"<channel>:<event>"`.

| Event | Channel | Payload | Meaning |
|---|---|---|---|
| `dispatch.ride_assigned` | `booking.{id}` · `user.{id}` | `{ booking_id, driver_id, office_id }` | a driver accepted — show driver |
| `booking.status_changed` | `booking.{id}` · `user.{id}` | `{ booking_id, status }` | matching→arriving→arrived→on_trip→completed / rejected / cancelled — **driven live by DriverX** ✅ |
| `driver.location` | `booking.{id}` · `user.{id}` | `{ booking_id, driver_id, lat, lng }` | animate car pin — **DriverX posts this** ✅ |
| `dispatch.job_cancelled` | `booking.{id}` · `user.{id}` | `{ booking_id, cancelled_by, reason }` | driver/office cancelled ✅ |
| `booking.meter_tick` | `booking.{id}` | `{ elapsed_s, distance_m, amount_minor }` | live meter (Ride/Premium) 🟡 client-derived |
| `booking.chat_message` | `booking.{id}` | `{ message_id, from, body }` | masked driver chat ✅ |
| `ride.released` | `booking.{id}` · `user.{id}` | `{ booking_id, total_minor, payment_method }` | completed & settled |
| `wallet.credited` | `user.{id}` | `{ amount_minor, currency_code }` | top-up / refund succeeded |
| `chat.message_created` | `user.{id}` | `{ conversation_id, body }` | office replied (office chat) |
| `rating.received` | `user.{id}` | `{ booking_id, stars }` | driver rated the rider |
| `support.reply` | `user.{id}` | `{ ticket_id }` | support ticket updated |

Each also lands as a durable, bilingual in-app notification + FCM push (`POST /devices` ✅, `GET /notifications` ✅). Socket delivery is best-effort — the notification + `GET /bookings/{id}` rehydrate is the guaranteed path.

---

# Build queue (v1 core → screens)

Already on the tested core (✅): quotes · hold · dispatch · refund · driver+rider rating · favorites · office chat · devices · notifications · wallet balance/topups · plans.

To build, in dependency order:
1. ~~**Auth** — OTP request/verify, social, session, `auth/me`~~ ✅ **built & tested** (2026-07-11, 11 tests).
2. ~~**Booking lifecycle** — `POST /bookings` (create+hold+dispatch), `GET /bookings/{id}`, cancel, change-office, `booking.status_changed`~~ ✅ **built & tested** (2026-07-11, 11 tests). Live trip status + `driver.location` are now **driven by DriverX** (2026-07-13) — the driver app posts navigate/arrived/start/end and location; the rider receives them on `booking.{id}`+`user.{id}`. `meter_tick` is client-derived from the running trip.
3. ~~**Marketplace** — `POST /trip-options`, `POST /offices/available`, `GET /offices/{id}`, office rating~~ ✅ **built & tested** (2026-07-11, 7 tests). Office marketplace search + stricter supply filter remain.
4. ~~**Places** — autocomplete/reverse/recent (server-side Google key), saved places~~ ✅ **built & tested** (2026-07-11, 9 tests).
5. ~~**Home aggregator** + trip history + receipt read-models~~ ✅ **built & tested** (2026-07-11, 6 tests).
6. ~~**Driver chat** (masked, per-booking)~~ ✅ **built & tested** (2026-07-11, 6 tests).
7. ~~**Support** — tickets, safety report/SOS, share-trip, office call-info~~ ✅ **built & tested** (2026-07-11, 9 tests).
8. ~~**Account** — profile, avatar, safety contacts, payment methods, promos~~ ✅ **built & tested** (2026-07-11, 7 tests).
9. ~~**Scheduled** — offers, scheduled bookings, assignment timeline~~ ✅ **built & tested** (2026-07-11, 6 tests).
10. **Corporate/Family** — deferred (B2B; the core rider flow is complete).
