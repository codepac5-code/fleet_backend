# FleetOS Mobile APIs — Developer Reference

Clean API layer for the two mobile apps, built on the tested service core (`api/v1`). Separate from the legacy `/user` and `/driver` routes (which are left untouched).

- **Fleet Ride** (rider app) → [user-app.md](user-app.md)
- **DriverX** (driver app) → [driver-app.md](driver-app.md)
- **Realtime / Socket.IO** (both apps) → [realtime.md](realtime.md)

These docs are the contract the app developer builds against. They grow as new screens are defined.

---

## Base & versioning

```
Base URL:   https://api.fleetos.app
Prefix:     /api/v1
```

## Authentication

Passport OAuth2 bearer tokens. The rider app authenticates against the `user` guard, the driver app against the `driver` guard.

```
Authorization: Bearer <access_token>
```

## Required headers

| Header | When | Purpose |
|---|---|---|
| `Authorization: Bearer <token>` | all authed endpoints | user / driver identity |
| `X-Country: <iso2>` | all sharded resources | selects the country database (e.g. `qa`) |
| `Accept-Language: en \| ar` | optional | response + notification language (default `en`) |
| `Idempotency-Key: <uuid>` | all mutating money/dispatch ops | safe retries (no double effect) |
| `Content-Type: application/json` | request bodies | — |
| `Accept: application/json` | always | forces JSON error shape |

## Response envelope

Success:
```json
{ "data": { ... }, "meta": { ... } }
```
Error:
```json
{ "error": { "code": "insufficient_funds", "message": "...", "details": { ... } } }
```

## Common error codes

| code | HTTP | meaning |
|---|---|---|
| `validation_failed` | 422 | missing/invalid fields |
| `unauthorized` | 401 | missing/invalid token |
| `forbidden` | 403 | not allowed |
| `not_found` | 404 | resource missing |
| `insufficient_funds` | 422 | wallet balance too low |
| `already_assigned` | 409 | ride taken by another driver |
| `offer_expired` | 409 | ride offer expired |
| `tariff_not_found` | 404 | no active tariff for office+service |

## Money

All amounts are **integer minor units** + a currency code:
```json
{ "amount_minor": 4950, "currency_code": "USD" }   // = 49.50
```

## Realtime transport (events)

The apps connect to the realtime gateway over Socket.io and subscribe to their own channel; the server pushes durable notifications (in-app + FCM push) in parallel.

- Gateway: `wss://rt.fleetos.app` (port 6002). Connect with `io(url, { auth: { token: <passport-token> } })`.
- Subscribe: `socket.emit('subscribe', '<channel>', ack)` — the server authorizes ownership before joining (a client may only join its own `user.{id}` / `driver.{id}` and booking channels it is a party to).
- Receive: `socket.on('<channel>:<event>', data => ...)`.
- Channels: `user.{id}` · `driver.{id}` · `office.{id}` · `booking.{id}`.

> Socket delivery is best-effort (a client offline at emit time misses the socket message) — the durable in-app notification + FCM push is the guaranteed path. Never rely on the socket alone for anything critical.

See each app doc for the exact events it receives.

## Pagination

```
GET /resource?cursor=<opaque>&limit=20
→ "meta": { "next_cursor": "...", "has_more": true }
```
