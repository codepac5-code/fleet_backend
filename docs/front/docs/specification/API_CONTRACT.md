# FleetOS DriverX — Backend Data Contract

Human-readable mirror of `docs/api_contract.json`. **Every attribute below conforms
to the real Eloquent models (`docs/Models/`) and migrations (`docs/migrations/`)** —
these are the actual DB columns, not app-friendly aliases. The Flutter app maps them
to its camelCase models via a thin mapper (see `flutter_mapping` in the JSON, and the
per-shape notes here).

- **Transport:** REST (JSON) for request/response, **Socket.IO** for realtime. No Firebase / no BaaS.
- **Auth:** phone-OTP → Bearer token (Laravel `driver` guard; stored in `flutter_secure_storage`).
- **Base URL:** `https://api.fleetos.example/driver/v1` (REST), `wss://rt.fleetos.example` (Socket.IO).
- **Naming (mixed by table era):** legacy tables (`drivers`, `users`, `vehicles`,
  `offices`) use **camelCase** columns; new rider-API tables (`ride_bookings`,
  `driver_presence`, `saved_places`, `ride_ratings`, `dispatch_*`, `app_notifications`,
  `wallet_*`, `payout_requests`, `driver_safety_events`, `lost_items`) use **snake_case**.
  Keep each field exactly as its model declares it.
- **Money:** new tables store **integer minor units** + a sibling `currency_code`
  (`total_minor: 3800` + `currency_code: "QAR"` == 38.00 QAR). Legacy tables
  (`drivers.walletBalance`, `commission_earnings.total_fare`) use **decimal major** units.
- **Distance/time:** `ride_bookings.distance_m` = metres, `duration_s` = seconds.
- **IDs:** numeric bigint PKs. Display labels like `DX-20498` are client-side only.
- **Enums:** there are **no DB enums** — status/class columns are plain `VARCHAR(16/32)`
  with a default; the DB will not reject a bad value, so the app and backend must agree
  the vocabulary in code (§0). Mind the length caps.
- **Offline queue (Hive):** ratings, tickets, status changes, found-item and cancel
  reports are queued offline and replayed on reconnect (`idempotency_key` required).
- **Common headers (requests):**
  ```
  Authorization: Bearer <accessToken>
  Content-Type: application/json
  X-Device-Id: <uuid>            // app install id
  X-Idempotency-Key: <uuid>      // POSTs that mutate money/state
  Accept-Language: en | ar
  ```
- **Common error envelope (responses):**
  ```json
  { "error": { "code": "BOOKING_NOT_ASSIGNABLE", "message": "…", "retryable": false } }
  ```

---

## 0. Core data models (shared response shapes)

### Driver — table `drivers` (camelCase)
`email`/`userName`/`status` added in migration `2026_07_15_000002`.
```json
{
  "id": 20498,
  "firstName": "John",
  "lastName": "Smith",
  "email": "john@example.com",
  "userName": "john.smith",
  "status": "active",              // pending | active | suspended  (default 'active')
  "phoneNumber": "5500XXXX",
  "dialCode": "+974",
  "officeId": 12,
  "vehicleId": 2049,
  "rating": 4.9,
  "rideCount": 1284,
  "kmCount": 20431,
  "walletBalance": 640.0,          // decimal, major units
  "isActive": true,
  "isConected": true,
  "is_registered": true,
  "photo": null,
  "gender": "male",
  "office":  { "id": 12, "officeName": "Pearl Mobility", "contactNumber": "+97440001234" },
  "vehicle": { "id": 2049, "model": "Camry", "plate": "QTR-2049", "color": "White" }
}
```
> Flutter `Driver`: `name = firstName+' '+lastName`, `office = office.officeName`,
> `officeHotline = office.contactNumber`, `vehicle = vehicle.model`,
> `plate = vehicle.plate`, `verified = status == 'active'`.

### Trip — table `ride_bookings` (snake_case)
Money is minor units; coords `decimal(10,7)`; `distance_m` metres, `duration_s` seconds.
The `rider` block is **derived** server-side (from `users` + `ride_ratings`) — there is
no rider name/rating column on `ride_bookings`.
```json
{
  "id": 20498,
  "user_id": 88231,
  "driver_id": 20498,
  "office_id": 12,
  "vehicle_id": 2049,
  "source": "dispatch",            // app | office | dispatch | scheduled  (default 'app')
  "service": "ride",
  "service_class": "comfort",      // ride | standard | comfort | premium | luxury
  "pricing_style": "fixed",        // fixed | meter
  "status": "assigned",            // default 'matching'; see enum below
  "pickup_lat": 25.2867,
  "pickup_lng": 51.5333,
  "pickup_title": "Msheireb Downtown Gate 3",
  "pickup_note": null,
  "dropoff_lat": 25.3210,
  "dropoff_lng": 51.5290,
  "dropoff_title": "West Bay Tower 18",
  "distance_m": 1200,
  "duration_s": 540,
  "currency_code": "QAR",
  "fare_minor": 3800,
  "discount_minor": 0,
  "waiting_minor": 0,
  "tip_minor": 0,
  "total_minor": 3800,
  "held_minor": 0,
  "payment_method": "wallet",      // cash | wallet | card  (default 'wallet')
  "scheduled_at": null,
  "passengers": 1,
  "luggage": 0,
  "flight_no": null,
  "assigned_at": "2026-07-15T13:02:20Z",
  "completed_at": null,
  "cancelled_at": null,
  "cancel_reason": null,
  "rider": { "name": "Amina M.", "rating": 4.91, "verified": true }
}
```
> Flutter `Trip`: `phase ⇄ status`, `pickupLabel = pickup_title`,
> `dropoffLabel = dropoff_title`, `distanceKm = distance_m/1000`,
> `fareQar = total_minor/100`, `meteredFare = pricing_style == 'meter'`,
> `pickupEtaMinutes = duration_s/60` (or live `location:tick`).

### Enums (VARCHAR, not DB enums — agree in code; `default`/`maxlen` from migrations)
| Column | default | maxlen | Values |
|---|---|---|---|
| `ride_bookings.status` | `matching` | 16 | `matching · offered · assigned · arrived · in_progress · completed · cancelled · no_show · scheduled` |
| `ride_bookings.service_class` | — | 32 | `ride · standard · comfort · premium · luxury` |
| `ride_bookings.pricing_style` | — | 16 | `fixed · meter` |
| `ride_bookings.payment_method` | `wallet` | 16 | `cash · wallet · card` |
| `ride_bookings.source` | `app` | 16 | `app · office · dispatch · scheduled` |
| `driver_presence.status` | `offline` | 16 | `online · busy · offline · on_trip` |
| `driver_presence.busy_reason` | null | 16 | `break · fuel · vehicle_check · prayer · personal` |
| `drivers.status` | `active` | 16 | `pending · active · suspended` |
| `driver_documents.status` | `pending` | — | `pending · verified · rejected · expired` |
| `driver_safety_events.status` | `open` | 16 | `open · active · closed · cancelled` |
| `dispatch_offers.status` | `offered` | 16 | `offered · accepted · declined · expired` |

> The app's `navigatingToPickup` and `payment` are **client-only** sub-states — on the
> wire they fold into `assigned` / `in_progress` (both fit the 16-char cap).

---

## 1. Auth & onboarding  (Splash → OTP → Permissions)

| Screen / action | Method + path | Request body | Response body |
|---|---|---|---|
| Request OTP | `POST /auth/otp/request` | `{ "phone": "+9745500XXXX", "purpose": "login\|reset" }` | `{ "sent": true, "expiresInSec": 120, "channel": "sms" }` |
| Verify OTP | `POST /auth/otp/verify` | `{ "phone": "+9745500XXXX", "code": "472189" }` | `{ "accessToken":"…", "refreshToken":"…", "expiresInSec":3600, "driver": { Driver }, "accountStatus": "active\|pending" }` |
| Refresh token | `POST /auth/token/refresh` | `{ "refreshToken": "…" }` | `{ "accessToken":"…", "expiresInSec":3600 }` |
| Logout | `POST /auth/logout` | `{ "refreshToken": "…" }` | `{ "ok": true }` |
| Register as driver → `driver_applications` | `POST /drivers/applications` | `{ "name":"John Smith", "phone":"+97455001234", "city":"Doha, Qatar", "vehicle_type":"Standard sedan", "license_number":"QA-DR-10284", "office_id":12, "invite_code":"PRL-4098", "kind":"invited" }` | `{ "id":1021, "status":"pending" }` |
| Link to taxi office | `POST /offices/link-requests` | `{ "invite_code":"PRL-4098", "office_id":12 }` | `{ "linkRequestId":331, "office":{ Office }, "status":"pending" }` |
| Account status poll | `GET /drivers/me/onboarding` | — | `{ "accountStatus":"pending", "office":{ "id":12, "officeName":"Pearl Mobility", "linkStatus":"pending" } }` |
| Report permissions | `PATCH /drivers/me/permissions` | `{ "locationAlways":true, "notifications":true, "motion":false }` | `{ "ok": true }` |
| Register push token → `app_device_tokens` | `POST /devices` | `{ "platform":"apns", "token":"…", "owner_type":"driver" }` | `{ "id":551, "platform":"apns", "last_seen_at":"…" }` |

---

## 2. Availability  (Home dock / Status sheet)  → `driver_presence`

| Action | Method + path | Request | Response |
|---|---|---|---|
| Readiness check | `GET /availability/readiness` | — | `{ "vehicle":true, "office":true, "wallet":true, "ready":true, "checks":"2/3" }` |
| Set availability | `POST /availability` | `{ "status":"busy", "busy_reason":"prayer", "lat":25.3, "lng":51.5 }` | `{ "driver_id":20498, "status":"busy", "busy_reason":"prayer", "heartbeat_at":"…" }` |
| Presence heartbeat | `POST /presence` | `{ "lat":25.2867, "lng":51.5333, "geohash":"thq7g", "heading":118.5, "speedKmh":42.0 }` | `{ "accepted": true }` |

> `status='on_trip'` is server-set on assignment — never sent by the client. Mirrored on
> the socket as `driver:availability` / `driver:presence` (§8). REST is the source of truth.

---

## 3. Live trip lifecycle  (mostly Socket.IO — see §8; REST fallbacks below)  → `ride_bookings`

Driver actions are emitted over the socket **and** POSTed as idempotent REST fallbacks
(`X-Idempotency-Key`). `{id}` is the `ride_bookings.id`. Every action returns the updated booking.

| Action | Method + path | Request | Response |
|---|---|---|---|
| Accept offer | `POST /trips/{id}/accept` | `{ "offer_id":77123, "lat":25.28, "lng":51.53 }` | `{ "booking":{ …"status":"assigned" }, "presence_status":"on_trip" }` |
| Reject / expire | `POST /trips/{id}/reject` | `{ "offer_id":77123, "reason":"declined" }` | `{ "ok": true }` |
| Start navigation | `POST /trips/{id}/navigate` | `{}` | `{ "booking":{ …"status":"assigned" } }` |
| Arrived at pickup | `POST /trips/{id}/arrive` | `{ "arrived_at":"…" }` | `{ "booking":{ …"status":"arrived" } }` |
| Start trip | `POST /trips/{id}/start` | `{ "started_at":"…" }` | `{ "booking":{ …"status":"in_progress" } }` |
| Reach destination | `POST /trips/{id}/reach` | `{}` | `{ "booking":{ …"total_minor":4150 } }` |
| Collect payment / complete | `POST /trips/{id}/complete` | `{ "payment_method":"wallet", "total_minor":4150, "collected":true }` | `{ "booking":{ …"status":"completed" }, "commission":{ CommissionEarning }, "wallet":{ WalletBalance }, "presence_status":"online" }` |
| Cancel (before in_progress) | `POST /trips/{id}/cancel` | `{ "cancel_reason":"Rider not reachable", "note":"…", "photo":null }` | `{ "booking":{ …"status":"no_show" }, "impact":{ "acceptanceBefore":96, "acceptanceAfter":94, "protected":true, "feeQar":0 } }` |

**Cancellation impact preview:** `GET /trips/{id}/cancel-impact?reason=Vehicle%20issue` →
`{ "acceptanceBefore":96, "acceptanceAfter":94, "weeklyRemaining":"1 of 3", "protected":true, "feeQar":0 }`

Cancel is allowed only for `status ∈ {scheduled, assigned, arrived}` (sets `cancelled`/`no_show`,
`cancelled_at`, `cancel_reason`). `cancel_reason` is a free string; the app offers:
`Rider not reachable | Wrong pickup | Vehicle issue | Emergency | Accepted by mistake | Other`.

### Trip chat (driver ↔ rider)  → `booking_chat_messages`

Per-ride chat scoped to the `trip:{booking_id}` room, meaningful only while a trip is
active. Realtime over the socket (`chat:*`, §8); REST below is history + offline replay.

| Action | Method + path | Request | Response |
|---|---|---|---|
| Chat history | `GET /trips/{id}/messages` | `?cursor=&limit=50` | `{ "items":[ { BookingChatMessage } ], "nextCursor":null }` |
| Send message | `POST /trips/{id}/messages` | `{ "body":"On my way, 3 min.", "client_msg_id":"c_9f2c1a" }` | `{ BookingChatMessage, "from_type":"driver" }` |
| Mark read | `POST /trips/{id}/messages/read` | `{ "up_to_id":55201 }` | `{ "ok":true, "read_at":"…" }` |

`BookingChatMessage` (real columns): `{ "id", "booking_id", "from_type":"driver\|rider\|office", "body", "read_at", "created_at" }` (`from_type` is `VARCHAR(8)`). For the persistent rider↔office thread use `chat_conversations` + `chat_messages` instead.

---

## 4. Trip records  (History / Ride details / Found item)

| Screen | Method + path | Request (query) | Response |
|---|---|---|---|
| Ride history | `GET /trips/history` | `?status=all\|completed\|cancelled&q=<text>&cursor=` | `{ "items":[ { RideBooking } ], "nextCursor": null }` |
| Ride details | `GET /trips/{id}` | — | `{ "booking":{ RideBooking }, "rider":{ "name","rating" }, "rating":{ RideRating } }` |
| Report found item → `lost_items` | `POST /trips/{id}/found-items` | `{ "category":"phone", "description":"Black iPhone…", "share_masked_number":true, "photo":null }` | `{ "id":2210, "status":"open", "booking_id":20498 }` |
| Rate rider → `ride_ratings` | `POST /trips/{id}/rating` | `{ "stars":5, "tags":["polite","on_time"], "comment":null, "book_again":true, "favorite":false }` | `{ RideRating }` |

> `lost_items` is a **rider-owned** table (`user_id`) with **no reporter column** — see
> `BACKEND_HANDOFF_NOTES.md §A` for how the driver-side write should be resolved.

---

## 5. Scheduled marketplace  (Marketplace / Upcoming)  → `ride_bookings` (source `scheduled`)

| Action | Method + path | Request | Response |
|---|---|---|---|
| Browse offers | `GET /scheduled/offers` | `?filter=available&zone=&cursor=` | `{ "items":[ { RideBooking, "status":"scheduled" } ], "nextCursor": null }` |
| Claim a trip | `POST /scheduled/offers/{id}/claim` | `{}` | `{ "booking":{ …"status":"scheduled" } }` |
| Upcoming committed | `GET /scheduled/committed` | `?cursor=` | `{ "items":[ { RideBooking } ], "summary":{ "booked":3, "estEarningsMinor":16100, "currency_code":"QAR", "missed":0 } }` |
| Release a booking | `POST /scheduled/committed/{id}/release` | `{ "reason":"Vehicle issue" }` | `{ "ok":true, "scoreImpact":true }` |
| Set reminder | `POST /scheduled/committed/{id}/reminder` | `{ "enabled":true, "leadMinutes":15 }` | `{ "ok": true }` |

> A claimed scheduled trip stays `status='scheduled'` (surfaced on the map) until
> `/navigate`, which locks presence to `on_trip`.

---

## 6. Earnings, wallet & notifications

| Screen | Method + path | Request | Response |
|---|---|---|---|
| Earnings dashboard | `GET /earnings` | `?range=today\|week\|month` | `{ "currency_code":"QAR", "gross_minor":22600, "fees_minor":2400, "wallet_earned_minor":10000, "cash_collected_minor":12600, "net_minor":20200, "chart_minor":[3800,6200,4400,7800,5800,9000,7000], "kpis":{ "acceptanceRate":92, "completionRate":98, "cancellationRate":2.1, "onTimePickup":94, "avgFareMinor":3220, "earningsPerHourMinor":5800, "onlineHours":"3h 54m", "tripsPerHour":1.8, "avgRating":4.91 } }` |
| Wallet → `wallet_balances` | `GET /wallet` | — | `{ "balance":{ "owner_type":"driver", "owner_id":20498, "currency_code":"QAR", "balance":640.0 }, "pending_payout_minor":64000, "nextPayout":"Tue", "bankMask":"•• 8842" }` |
| Wallet transactions → `wallet_transactions` | `GET /wallet/transactions` | `?cursor=` | `{ "items":[ { WalletTransaction } ], "nextCursor":null }` |
| Cash out → `payout_requests` | `POST /wallet/payouts` | `{ "amount_minor":64000, "currency_code":"QAR", "source_account":"wallet" }` | `{ "id":7781, "status":"processing", "amount_minor":64000 }` |
| Payment settings | `PATCH /drivers/me/payment` | `{ "autoWeeklyPayout":true, "acceptCash":true, "payoutBankId":"bank_123" }` | `{ "ok": true }` |
| Notifications → `app_notifications` | `GET /notifications` | `?filter=all\|unread&cursor=` | `{ "items":[ { AppNotification } ], "unread":2, "nextCursor":null }` |
| Mark all read | `POST /notifications/read` | `{ "all": true }` | `{ "ok":true, "unread":0 }` |

`WalletTransaction` (real columns): `{ "id", "from_type","from_id","to_type","to_id", "amount", "balance_before","balance_after", "status", "source_type","source_id", "description_en", "transaction_reference", "created_at" }` — money here is **decimal major** units.

`AppNotification` (real columns): `{ "id", "notifiable_type":"driver","notifiable_id":20498, "template_key", "type", "locale", "title", "body", "data":{ "route":"/upcoming" }, "read_at":null, "created_at" }`.

---

## 7. Profile, settings, places, support & safety

| Screen / action | Method + path | Request | Response |
|---|---|---|---|
| Get profile | `GET /drivers/me` | — | `{ Driver }` |
| Update account | `PATCH /drivers/me` | `{ "firstName","lastName","email","phoneNumber" }` | `{ Driver }` |
| Update vehicle → `vehicles` | `PATCH /drivers/me/vehicle` | `{ "model","plate","color","modelYear","seatsCount" }` | `{ Vehicle }` |
| Documents list → `driver_documents` | `GET /drivers/me/documents` | — | `{ "items":[ { "id","driverId","document_id","name","file","status":"verified","note","expires_at" } ] }` |
| Upload document | `POST /drivers/me/documents` | multipart `{ document_id, name, file, expires_at }` | `{ "id","status":"pending" }` |
| Language / preferences | `PATCH /drivers/me/preferences` | `{ "locale":"ar" }` | `{ "ok":true, "locale":"ar" }` |
| Saved places list → `saved_places` | `GET /places` | — | `{ "items":[ { "id","user_id","label":"home","icon":"home","title":"Home (end of shift)","address":"Al Sadd, Zone 38","lat":25.276,"lng":51.518 } ] }` |
| Add / edit place | `POST /places` · `PATCH /places/{id}` | `{ "label","icon","title","address","lat","lng" }` | `{ SavedPlace }` |
| Delete place | `DELETE /places/{id}` | — | `{ "ok": true }` |
| Report trip issue → `drivers_issues` | `POST /support/trip-issues` | `{ "booking_id":20498, "subject":"Payment issue", "description":"…", "photo":null }` | `{ "id":5521, "isClosed":false }` |
| Support ticket (office/FleetOS) | `POST /support/tickets` | `{ "channel":"fleetos", "subject":"App issue", "description":"…" }` | `{ "id":5521, "isClosed":false }` |
| Issue replies → `replies` | `GET /support/issues/{id}/replies` | — | `{ "items":[ { "id","issueId","sender_type","sender_id","senderName","content","imageUrl" } ] }` |
| Help articles → `help_suggestions` | `GET /help/articles` | `?q=` | `{ "items":[ { "id","title_en","description_en","category","read_minutes" } ] }` |
| Safety contacts → `safety_contacts` | `GET /safety/contacts` · `POST /safety/contacts` | `{ "name","phone","relation","is_primary","auto_share" }` | `{ SafetyContact }` |
| **SOS trigger** → `driver_safety_events` | `POST /safety/sos` | `{ "booking_id":20498, "kind":"sos", "category":"hold", "lat":25.28, "lng":51.53, "hold_ms":1500 }` | `{ "id":3391, "status":"open", "sharedWith":["fleetos","office","contacts"] }` |
| Share live status | `POST /safety/status-links` | `{ "booking_id":20498 }` | `{ "url":"https://track.fleetos.example/s/9f2c1a", "expiresAt":"…" }` |
| End emergency | `POST /safety/sos/{id}/end` | `{ "safeConfirmed":true }` | `{ "id":3391, "status":"closed" }` |
| Call actions (rider/office/999) | *no API* | — | opens OS dialer via `CallService` (`tel:`) — number from Trip/Driver/office payloads |

> `saved_places` + `safety_contacts` are **rider-owned** (`user_id`) in the current schema.
> Driver ownership is an open decision — see `BACKEND_HANDOFF_NOTES.md §A`.

---

## 8. Socket.IO realtime channel  (backed by `event_outbox`)

**Connect:** `wss://rt.fleetos.example` with handshake `auth: { token, driver_id }`.
**Rooms:** `driver:{driver_id}`, and `trip:{booking_id}` when a trip is active.
**Reconnect:** auto with backoff; on reconnect the app re-syncs via the REST fallbacks above.

### Client → Server (app emits)
| Event | Payload | Sent when |
|---|---|---|
| `driver:availability` | `{ "driver_id":20498, "status":"online", "busy_reason":null }` | dock/status change (`SocketService.setAvailability`) |
| `driver:presence` | `{ "driver_id":20498, "lat":25.28, "lng":51.53, "heading":118.5, "speedKmh":42.0 }` | periodic while online / on-trip |
| `trip:action` | `{ "booking_id":20498, "action":"accept" }` | slide-to-act; `action ∈ accept\|navigate\|arrive\|start\|complete\|cancel` |
| `chat:send` | `{ "booking_id":20498, "body":"On my way, 3 min.", "client_msg_id":"c_9f2c1a" }` | driver sends a rider chat message |
| `chat:read` | `{ "booking_id":20498, "up_to_id":55201 }` | driver read rider messages |
| `chat:typing` | `{ "booking_id":20498, "from_type":"driver", "typing":true }` | ephemeral typing indicator |

### Server → Client (app listens)
| Event | Payload | Maps to (code) → FSM |
|---|---|---|
| `trip:request` | `{ "booking":{ RideBooking, "status":"offered" }, "offer":{ DispatchOffer }, "countdownSeconds":18 }` | `IncomingRequestEvent` → `TripRequested` |
| `trip:ack` | `{ "booking_id":20498, "action":"accept" }` | `TripAckEvent` (informational) |
| `location:tick` | `{ "booking_id":20498, "etaMinutes":3, "distance_m":900 }` | `LocationTickEvent` → updates ETA/distance |
| `meter:tick` | `{ "booking_id":20498, "total_minor":3250, "distance_m":5400, "duration_s":660 }` | `MeterTickEvent` → live meter while `in_progress` |
| `trip:status` | `{ "booking_id":20498, "status":"cancelled" }` | force-sync trip FSM (office/rider cancel) |
| `office:message` | `{ AppNotification }` | notifications center |
| `chat:message` | `{ "message":{ BookingChatMessage }, "client_msg_id"? }` | `ChatMessageEvent` → appends to the trip chat |
| `chat:read` | `{ "booking_id":20498, "reader_type":"rider", "up_to_id":55187, "read_at":"…" }` | rider read receipts |
| `connection` (transport) | `connecting \| connected \| disconnected` | `SocketStatus` → reconnect banner |

`DispatchOffer` (real columns): `{ "id","booking_id","driver_id","wave","status":"offered","distance_m","expires_at" }` — the app's "incoming request" is this offer; `expires_at` drives the countdown.

---

## Endpoint summary

- **REST:** ~58 endpoints across 7 groups (auth, availability, trips, **chat**, scheduled,
  earnings/wallet/notifications, profile/settings/places/support/safety).
- **Socket.IO:** 6 client→server events, 8 server→client events (backed by `event_outbox`).
- **OS-level (no API):** phone calls via `CallService` (`tel:`), share sheet, image picker.

> Full per-column schemas + examples for all 26 tables live in `docs/api_contract.json`
> (`models` section). Open schema decisions for the backend are in
> `docs/BACKEND_HANDOFF_NOTES.md`.
