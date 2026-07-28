# Fleet Ride — Rider v2 Ride Lifecycle (Booking · Trips · Notifications)

Backend build notes for the app developer. Covers the endpoints and realtime events built on
`2026-07-16` against `docs/front/ff/` (`api_examples.json`, `BACKEND_SCENARIO_A_TO_Z.md`,
`REALTIME_APP_REQUIREMENTS.md`). All routes are served under the `/user` prefix, guarded by
`user-api` (envelope + locale) and `auth:user` (Passport). Every REST response uses the rich
envelope `{status, statusCode, message, data, error, meta, locale}`. Money is integer minor units
(`*_minor`) with a sibling `currency_code`.

## Booking (the live ride)

| Method | Path | Body | Success | Notes |
|---|---|---|---|---|
| POST | `/user/bookings` | flat: `service, service_class, office_id, pickup_lat, pickup_lng, pickup_title, pickup_note, dropoff_lat, dropoff_lng, dropoff_title, payment_method?, promo_code?, idempotency_key?` | `201` flat booking row | Idempotency via body `idempotency_key` **or** `Idempotency-Key` header. Default `payment_method=wallet` → `422 insufficient_funds` when the wallet can't cover the estimate (app shows the payment-recovery screen). `cash` skips the hold. |
| POST | `/user/bookings/{id}/cancel` | `reason?` | `200` flat row (`status=cancelled`, `cancel_reason`) | Refunds any escrow hold. |
| GET | `/user/bookings/{id}` | — | `200` flat row + `office` + `driver` | Snapshot for reconnect (S13). `status` is the **effective** FSM status (derives `assigned`/`completed` from dispatch/settlement). |

The booking row is the raw `ride_bookings` shape (all `*_minor`, ISO-8601-Zulu timestamps), matching
`api_examples.json`.

## Trips (history · receipt · rating · lost-item · chat)

| Method | Path | Notes |
|---|---|---|
| GET | `/user/trips?status=active\|past\|cancelled&cursor&limit` | `{items[], nextCursor}`; each item is the flat row + `office`. Opaque base64 cursor. Use `status=active` for the boot snapshot (S0). |
| GET | `/user/trips/{id}` | Flat row + `office` + `rating` + `driver`. Same handler as `GET /bookings/{id}` (receipt/detail). |
| POST | `/user/trips/{id}/rating` | Dual rating: `{stars, tags[], comment, bookAgain, favorite}` → rates **driver and office**, persists tags/bookAgain, adds the office to favorites when `favorite=true`, stamps `rated_at`. Emits `rating.received` to each ratee. → `{ok:true}`. |
| POST | `/user/trips/{id}/lost-item` | `{category, description, shareMaskedNumber}` → opens a support ticket + a lost-item record. → `201 {ticketId, status}`. |
| GET | `/user/trips/{id}/messages?cursor&limit` | In-ride chat, ascending. `{items[], nextCursor}`. |
| POST | `/user/trips/{id}/messages` | `{body}` → `201` message. Only allowed while a driver is assigned and the ride isn't settled. Emits `booking.chat_message`. |

## Notifications & devices

| Method | Path | Notes |
|---|---|---|
| GET | `/user/notifications?unread&cursor&limit` | `{items[], unreadCount, nextCursor}`. |
| POST | `/user/notifications/{id}/read` | Owner-only → updated row. |
| POST | `/user/notifications/read-all` | `{updated}`. |
| POST | `/user/devices` | `{token, platform: ios\|android\|web}` → `201 {id, token, platform, last_seen_at}`. Registered under owner `user` so pushes align with `user.{id}` notifications. |
| DELETE | `/user/devices/{token}` | `204`. Call on logout. |

## Realtime events (the wire)

REST write → transactional `event_outbox` → relay → Redis → gateway emits `"{channel}:{eventType}"`.
Rooms: `user.{id}` (whole session), `booking.{id}` (join after `POST /bookings`, leave on terminal
status + `ride.released`). The socket is a notifier — REST is authoritative; re-snapshot on reconnect.

**`booking.status_changed`** — canonical payload now emitted by **every** side of the FSM
(rider create/cancel, driver transitions, scheduled activation, office booking):

```jsonc
{ "booking_id": 4100, "status": "matching", "office_id": 12, "source": "rider", "at": "2026-07-16T09:12:03Z", "reason": "..." }
```
`status` ∈ `matching · assigned · arriving · arrived · on_trip · completed · cancelled · rejected · scheduled`
(one enum — **Q-3 resolved**). `source` ∈ `rider · driver · office · system`; `reason` present on
cancellations. Emitted on both `booking.{id}` and `user.{id}`.

| Event | Channel(s) | When |
|---|---|---|
| `booking.status_changed(matching)` | `booking.{id}`,`user.{id}` | POST /bookings |
| `dispatch.ride_assigned` | `booking.{id}`,`user.{id}`,`driver.{id}` | driver accept — `RideBooking.driver_id` is now persisted, so `GET /bookings/{id}` returns the driver (Q-4 path B) |
| `booking.status_changed(arriving/arrived/on_trip/completed)` | `booking.{id}`,`user.{id}` | driver transitions |
| `driver.location` | `booking.{id}`,`user.{id}` | `{driver_id, lat, lng, heading, eta_seconds, at}` (Q-6 enrichment) |
| `booking.meter` | `booking.{id}`,`user.{id}` | live fare during `on_trip` — event type + `BookingEvents::meter()` helper ready; driver side emits the ticks (Q-2) |
| `dispatch.ride_assigned` | `booking.{id}`,`user.{id}`,`driver.{id}`,`office.{id}` | now includes `user.{id}` |
| `booking.chat_message` | `booking.{id}` | `{booking_id, message_id, sender, sender_role, text, body, created_at}` |
| `chat.message_created` | recipient `user.{id}` | `{conversation_id, message_id, sender_role, preview, created_at}` (rider↔office) |
| `support.message_created` | `user.{id}` | `{ticket_id, body, created_at}` → app re-fetches thread |
| `ride.released` | `booking.{id}`,`driver.{id}`,`office.{id}` | settlement complete → fetch receipt, unsubscribe |
| `booking.status_changed(cancelled, source, reason)` | `booking.{id}`,`user.{id}` | cancel (rider/driver/office/system) |
| `rating.received` | ratee channel (`user.{id}` when the rider is rated) | `{booking_id, stars, from_role}` |
| `wallet.credited` | `user.{id}` | `{amount, currency, balance_after, reason, ref_id}` — emitted at payment/refund settlement |
| `payment.succeeded` | `user.{id}` | `{payment_id, booking_id, amount, currency, method}` — emitted at settlement |
| `notification.created` | `user.{id}` | `{id, type, template_key, title, body, data, read_at, unread_count}` — live bell badge (Q-6 delivered) |

## Wallet & payments

| Method | Path | Notes |
|---|---|---|
| GET | `/user/wallet?currency_code=` | `{balance (decimal), currency, symbol, decimals}`. |
| GET | `/user/wallet/transactions?cursor&limit` | `{items[], nextCursor}`. Amounts are signed decimals (credit = money in). |
| POST | `/user/wallet/topup` | `{amount (minor), paymentMethodId}` + `Idempotency-Key` → `{ledgerId, status, clientSecret, requiresAction}`. Records a ledger top-up intent; `clientSecret` requires live Stripe keys. |
| GET/POST | `/user/payment-methods` | List / save `{stripePaymentMethodId, setDefault}`. |
| PATCH/DELETE | `/user/payment-methods/{id}` | Set default / remove. |
| GET | `/user/promos` | Available promos. `POST /user/promos/redeem {code}` → `{applied, discount, discountType, message}`. |
| POST | `/user/payments/stripe/setup-intent` · `/payment-intent` | Stripe intents (gateway-gated; `503 payments_unavailable` until live keys are configured). |

## Scheduled (book-ahead)

`POST /user/scheduled` (body `{office_id, route:{pickup,dropoff,service,serviceClass}, scheduledFor, passengers, luggage, flightNo}`) · `GET /user/scheduled/{id}` · `PATCH /user/scheduled/{id}` (bumps `change_revision`) · `DELETE /user/scheduled/{id}`. Returns the flat booking row + a `steps` timeline. Near the pickup window the ride flips to `matching` and rejoins the live dispatch flow (Scenario D).

## Support · B2B · geocode

| Method | Path | Notes |
|---|---|---|
| GET/POST | `/user/tickets` (+ `GET /{id}`) | Two-layer support. `topic` maps to a layer (office ops vs FleetOS). |
| POST | `/user/complaints` | `{about, tripId, description, photoUrl}` → routed by `about` (**driver → office**, **safety → FleetOS + urgent**). |
| GET | `/user/help/articles` (+ `/{id}`) | Locale-aware help catalog. |
| GET | `/user/corporate/invoices` | Business-account monthly invoices (**corporate billing = office billing; companies ARE offices**). |
| GET/POST/PATCH/DELETE | `/user/family/members` | Family/guardian riders. |
| POST | `/user/geocode/reverse` | `{lat, lng}` → `{address}`. |

## Auth (session)

| Method | Path | Notes |
|---|---|---|
| POST | `/user/auth/social` | `{provider: google\|apple, token}` → verify provider token → issue token pair + user (new or existing, matched by phone from the provider profile). Returns `422 social_unavailable` until a real Google/Apple verifier is configured. |
| POST | `/user/auth/phone/change` | Authenticated. `{dialCode, phone}` → OTP challenge to the new number (`{challengeId, expiresIn}`). Rejects a number already in use (`409 phone_taken`). |
| POST | `/user/auth/phone/change/verify` | Authenticated. `{challengeId, code}` → applies the new phone, returns the updated user. |

## Open items — ops only (all endpoints built; 70/70 live)

- Configure a live Stripe secret key so topup / setup-intent / payment-intent return real client secrets (endpoints + ledger records already wired; card gateway is currently `NullCardGateway`).
- Configure a real Google/Apple `SocialVerifier` (interface bound to `UnconfiguredSocialVerifier`).
- **Q-2** — have the driver side emit `booking.meter` ticks (rider event type + `BookingEvents::meter()` helper are already in place).
