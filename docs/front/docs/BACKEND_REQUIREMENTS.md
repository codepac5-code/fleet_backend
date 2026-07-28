# Fleet Ride (Rider App) — Backend Requirements

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
