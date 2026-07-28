# FleetOS DriverX — API Specification & Gap Report

**For the backend developer.** This folder is the machine-readable contract for the
DriverX (driver) app plus the completeness audit behind it.

| File | What it is |
|---|---|
| `openapi.yaml` | OpenAPI 3.0.3 spec — all 72 REST endpoints, 27 model schemas, bearer auth, error envelope, and the Socket.IO events under `x-realtime`. Import into Swagger UI / codegen. |
| `fleet_driver.postman_collection.json` | Postman v2.1 collection — every endpoint grouped in 7 folders, `{{baseUrl}}`/`{{accessToken}}` variables, collection-level bearer auth. "Auth ▸ Verify OTP" auto-captures the token. |
| `README.md` (this file) | The gap report: what was audited, what was missing, and the open decisions. |

Source of truth for field names/types stays **`../api_contract.json`** (+ `../API_CONTRACT.md`
human mirror). These two files are **generated from it** and kept in sync. Schema
decisions/blockers live in **`../BACKEND_HANDOFF_NOTES.md`** (still current — read it too).

- **Base URL:** `https://api.fleetos.example/driver/v1` · **Realtime:** `wss://rt.fleetos.example` (Socket.IO)
- **Auth:** phone-OTP → Bearer (Laravel `driver` guard). Send `Authorization: Bearer <token>` on all but the `/auth/*` onboarding endpoints.
- **Money:** integer **minor units** + `currency_code` on new tables; decimal major on legacy. Never mix.

---

## What was audited

Every screen, service, socket handler, form and button in `lib/` was inventoried and
diffed against the existing contract (then 58 endpoints). The app is **100% mock-driven
today** (no HTTP client exists yet), so each endpoint is an inferred need traced to a
concrete UI trigger. Result: the contract already covered the full trip lifecycle,
availability, chat, scheduled, earnings/wallet, notifications, profile/settings,
support and safety. The audit found **14 additions** (10 real gaps + 4 decisions),
now merged into `api_contract.json` (v2.1.0) and both generated files.

## Gaps found & fixed → implement these (10)

Each is tagged `[GAP]` in `openapi.yaml` / `api_contract.json`.

| # | Endpoint | App trigger (why it's needed) |
|---|---|---|
| 1 | `GET /trips/{booking_id}/rider-contact` | Active-trip **"Rider" call button** (`home_screen` `_callRider`) — needs an office-masked/proxy number; the trip payload carries no rider phone. |
| 2 | `POST /drivers/me/deletion-requests` | **"Delete account"** (`records_screens.dart:434`) is a snackbar today ("requires office confirmation"). Needs an office-confirmed deletion request. |
| 3 | `PATCH /places/{id}` | **Edit saved place** ("Save place"). Was in `API_CONTRACT.md` but missing from the JSON collection. |
| 4 | `GET /places/search` | Saved-place editor **address search / drop-a-pin** (geocode/autocomplete). |
| 5 | `GET /help/articles/{id}` | **Help article detail** — the list endpoint returns summaries only. |
| 6 | `POST /support/issues/{id}/replies` | Issue thread showed replies (GET) but the driver also needs to **post a reply**. |
| 7 | `PATCH /safety/contacts/{id}` | **Edit** an emergency contact (GET/POST existed; no update). |
| 8 | `DELETE /safety/contacts/{id}` | **Remove** an emergency contact. |
| 9 | `DELETE /safety/status-links/{id}` | **"Stop sharing"** live status (POST created the link; nothing revoked it). |
| 10 | `GET /drivers/me/summary` | Home **availability-dock KPIs** (Today / Trips / Online / Wallet) — a light summary so the dock needn't load the full `/earnings` dashboard. |

## Decisions — confirm before coding (4)

Tagged `[DECISION]`. Added to the spec so they're not forgotten, but each needs a yes/no.

| # | Endpoint | Decision |
|---|---|---|
| 1 | `POST /auth/login` | SignIn shows a **password field** and `drivers.password` exists, but the wired flow is OTP-only. Keep password login or drop the field? |
| 2 | `POST /auth/password/reset` | ForgotPassword sends a reset code via OTP. Is there a real **set-new-password** step, or is "reset" just OTP re-login? |
| 3 | `GET /zones/demand` | The map **"Airport queue · low wait"** pill is hardcoded. Serve demand hints via REST, push via a `zone:demand` socket event, or drop for v1? |
| 4 | `POST /auth/otp/request` (resend) | **"Resend code"** reuses Request OTP — confirm the per-phone rate-limit policy. |

## Out of scope — no backend endpoint (client-side / 3rd-party)

- **Map tiles, route polyline, turn-by-turn** → Google Maps SDK / Directions on the client.
- **Phone calls** → `tel:` via `CallService`; the number comes from payloads (office `contactNumber`, or #1 above for riders).
- **Analytics events** → client SDK; add a server sink (`POST /analytics/events`) only if you want first-party analytics.
- **Push delivery** → APNs/FCM; the app only **registers** the token (`POST /devices`, already in the contract).

## Still-open schema blockers (from `../BACKEND_HANDOFF_NOTES.md` — unchanged)

These pre-date this audit and remain the only **hard** blockers:

- **§A Ownership:** `saved_places`, `safety_contacts`, `lost_items` are **rider-owned** (`user_id`) but are DriverX features. Add polymorphic ownership / a `driver_id`, or confirm every driver has a linked `users` row. (Affects endpoints #3, #4, #7–9 above and found-items.)
- **§B Rider summary:** the Trip Request card needs rider `name/rating/verified`, but `ride_bookings` has only `user_id` — derive a rider block server-side on the trip endpoints and the `trip:request` socket event.

## Realtime (Socket.IO) — unchanged, documented in `openapi.yaml` `x-realtime`

6 client→server events (`driver:availability`, `driver:presence`, `trip:action`,
`chat:send`, `chat:read`, `chat:typing`) and 8 server→client
(`trip:request`, `trip:ack`, `location:tick`, `meter:tick`, `trip:status`,
`office:message`, `chat:message`, `chat:read`). Backed by the `event_outbox` table.

---

## Endpoint totals

- **REST:** 72 across 7 groups (auth 13 · availability 4 · trips 14 · chat 3 · scheduled 5 · earnings/wallet/notifications 8 · profile/settings/places/support/safety 25).
- **Socket.IO:** 6 client→server + 8 server→client.
- **Models:** 27 schemas.
