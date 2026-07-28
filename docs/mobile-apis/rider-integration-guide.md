# Fleet Ride — Rider API Integration Guide (for the front-end developer)

This document explains **how to call the rider (Fleet Ride) backend**: the base URL, the URL prefix,
required headers, the response envelope, the authentication flow, error handling, and the realtime
socket connection. It answers the common question: *"the contract shows `/v1/...`, what do I actually call?"*

---

## 1. Base URL & prefix — the one thing to get right

The OpenAPI/`api_examples.json` contract lists a placeholder server `https://fleetapp.net/v1`.
**Ignore the `/v1` part.** In this backend every rider endpoint is served under the **`/user`** prefix.

```
Base URL  =  {SCHEME}://{HOST}/user
```

| Environment | Base URL |
|---|---|
| Local dev | `http://127.0.0.1:8000/user` |
| Staging / Prod | `https://{host}/user` |

**Mapping — how the contract path becomes the real path** (drop `/v1`, prepend the base URL):

| Contract (in api_examples.json) | Real endpoint you call |
|---|---|
| `POST /v1/auth/otp/request` | `POST {base}/user/auth/otp/request` |
| `POST /v1/auth/otp/verify` | `POST {base}/user/auth/otp/verify` |
| `GET /v1/me` | `GET {base}/user/me` |
| `POST /v1/bookings` | `POST {base}/user/bookings` |
| `GET /v1/wallet` | `GET {base}/user/wallet` |
| `GET /v1/trips?status=past` | `GET {base}/user/trips?status=past` |

> **Rule of thumb:** take any path from the contract, remove the leading `/v1`, and prefix it with
> your configured base URL that already ends in `/user`. So set your HTTP client's `baseUrl` to
> `https://{host}/user` and then call `/auth/otp/request`, `/me`, `/bookings`, … exactly as written
> in the contract (without `/v1`).

There is **no `/api`** segment. It is `/user/...`, not `/api/user/...` or `/api/v1/...`.

---

## 2. Required headers

| Header | When | Value / notes |
|---|---|---|
| `Content-Type` | all requests with a body | `application/json` |
| `Accept` | all requests | `application/json` |
| `Authorization` | every endpoint **except** `/auth/otp/*`, `/auth/register`, `/auth/refresh`, `/auth/social` | `Bearer {accessToken}` |
| `X-Country` | all requests | ISO-2 country code (e.g. `QA`). Selects the country database (shard). Send the same value in the socket handshake. |
| `Accept-Language` | all requests | `en` or `ar`. The resolved language is persisted per user and echoed back as `locale`. |
| `Idempotency-Key` | create/pay POSTs (`/bookings`, `/wallet/topup`, `/payment-methods`, `/scheduled`, stripe intents) | a client-generated UUID; safe to retry. `/bookings` also accepts a body field `idempotency_key`. |

---

## 3. Response envelope (every REST response)

Every REST response — success or error — uses this envelope:

```jsonc
{
  "status": true,            // boolean success flag
  "statusCode": 200,         // mirrors the HTTP status
  "message": "OK",           // localized human message
  "data": { /* ... */ },     // the payload (object, array, or null)
  "error": null,             // on failure: { code, message, details? }
  "meta": null,              // pagination etc. when present
  "locale": "en"             // resolved language
}
```

- **Money** is always **integer minor units** (fields ending in `*_minor`) paired with a sibling
  `currency_code`. Example: `total_minor: 2400`, `currency_code: "SAR"` → 24.00 SAR. The only
  exception is the wallet balance summary (`GET /wallet`) which returns a decimal `balance` plus
  `symbol` and `decimals`.
- `204 No Content` responses (logout, deletes, favorites add/remove) have **no body**.
- Cursor pagination: list responses return `{ items: [...], nextCursor }`. Pass `?cursor={nextCursor}&limit=20`
  to page. `nextCursor` is `null` on the last page.

---

## 4. Authentication flow (OTP → token)

```
1) POST /user/auth/otp/request   { dialCode, phone, country }
      → 200 { challengeId, expiresIn, isNewUser }
      (an OTP is sent to the phone)

2a) Existing user (isNewUser=false):
    POST /user/auth/otp/verify    { challengeId, code }
      → 200 { accessToken, refreshToken, tokenType:"Bearer", expiresIn, user }

2b) New user (isNewUser=true): verify first, then complete the profile:
    POST /user/auth/otp/verify    { challengeId, code }        → token pair
    POST /user/auth/register      { challengeId, fullName, email, country }  → token pair + user

3) Use the token on every other call:
    Authorization: Bearer {accessToken}

4) When the access token expires:
    POST /user/auth/refresh       { refreshToken }
      → 200 { accessToken, refreshToken, tokenType, expiresIn }   (refresh token rotates — store the new one)

5) Social login (Google/Apple):
    POST /user/auth/social        { provider:"google"|"apple", token }   → token pair + user

6) Sign out:  POST /user/auth/logout   → 204   (then drop the token & disconnect the socket)
```

- Tokens are **Laravel Passport** JWT access tokens. The refresh token is single-use (rotating) —
  always persist the newest one returned.
- On any `401 unauthenticated` from a protected endpoint → try `/auth/refresh`; if that also fails,
  send the user back to login.

---

## 5. HTTP status codes & errors

| Status | Meaning |
|---|---|
| `200` | OK |
| `201` | Created (e.g. `POST /bookings`, `POST /me/places`) |
| `204` | Success, no body (logout, deletes, favorites) |
| `401` | Missing/expired token → refresh or re-login |
| `404` | Not found / not owned by this user |
| `409` | Conflict (e.g. `already_assigned`, `phone_taken`) |
| `422` | Validation failed or a domain rule (e.g. `insufficient_funds`, `tariff_not_found`) |

Error body:

```jsonc
{
  "status": false,
  "statusCode": 422,
  "message": "The given data was invalid.",
  "error": { "code": "validation_failed", "message": "...", "details": { "field": ["..."] } },
  "data": null, "meta": null, "locale": "en"
}
```

Always branch on `error.code` (stable machine string), not on the localized `message`.

---

## 6. Realtime (Socket.IO)

The live trip is driven by a Socket.IO gateway (the app never sends domain actions over the socket —
**every action is a REST call**; the socket only *notifies*).

- **Connect:** `wss://{host}:{FLEET_RT_PORT}` (default port `6002`), handshake
  `auth: { token: {accessToken}, country: {ISO-2} }` — read the token fresh on every (re)connect.
- **Subscribe to rooms:**
  - `user.{userId}` — right after connect, for the whole session.
  - `booking.{bookingId}` — right after `POST /bookings` succeeds (or when a snapshot shows an active
    ride); leave on any terminal `booking.status_changed` + `ride.released`.
- **Events are named** `"{channel}:{eventType}"`. The rider consumes: `booking.status_changed`,
  `dispatch.ride_assigned`, `driver.location`, `booking.meter`, `booking.chat_message`,
  `ride.released`, `chat.message_created`, `support.message_created`, `wallet.credited`,
  `payment.succeeded`, `rating.received`, `notification.created`.
- **Redis does not replay:** on every (re)connect, re-subscribe **and** snapshot via REST
  (`GET /trips?status=active`, `GET /bookings/{id}`, `GET /wallet`) to reconcile.

Full event payloads + the trip state machine are in
[`rider-v2-ride-lifecycle.md`](rider-v2-ride-lifecycle.md).

---

## 7. Worked example (curl)

```bash
BASE=http://127.0.0.1:8000/user

# 1) request OTP
curl -X POST $BASE/auth/otp/request \
  -H "Content-Type: application/json" -H "Accept: application/json" -H "X-Country: QA" \
  -d '{"dialCode":"+974","phone":"55500500","country":"QA"}'
# → { "data": { "challengeId": "chg_...", "expiresIn": 300, "isNewUser": false } }

# 2) verify → token
curl -X POST $BASE/auth/otp/verify \
  -H "Content-Type: application/json" -H "Accept: application/json" -H "X-Country: QA" \
  -d '{"challengeId":"chg_...","code":"1234"}'
# → { "data": { "accessToken": "eyJ...", "refreshToken": "...", "tokenType":"Bearer", "user": {...} } }

# 3) authenticated call
curl $BASE/wallet \
  -H "Authorization: Bearer eyJ..." -H "Accept: application/json" -H "X-Country: QA"
# → { "data": { "balance": 500, "currency": "SAR", "symbol": "ر.س", "decimals": 2 } }
```

---

## 8. Local test account (seeded)

A ready test rider exists after running `php artisan db:seed --class=RiderTestDataSeeder`:

| Field | Value |
|---|---|
| Phone | `+974 55500500` (dialCode `+974`, phone `55500500`) |
| Wallet | 500.00 SAR |
| Data | 3 past trips, 1 scheduled ride, 3 notifications, 1 support ticket, 2 family members, 2 corporate invoices, 1 saved card, offices + online drivers |

In dev, the OTP code is written to the cache under `rider:challenge:{challengeId}` (no SMS gateway).

---

### TL;DR for the front-end

- **Base URL:** `https://{host}/user` — then use contract paths **without `/v1`**.
- Send `Authorization: Bearer`, `X-Country`, `Accept-Language`, `Content-Type: application/json`.
- Everything is enveloped `{status, statusCode, message, data, error, meta, locale}`; money is `*_minor` + `currency_code`.
- Auth = OTP challenge → verify → Bearer token (+ rotating refresh).
- Live trip = REST writes + Socket.IO notifications on `user.{id}` / `booking.{id}`.
