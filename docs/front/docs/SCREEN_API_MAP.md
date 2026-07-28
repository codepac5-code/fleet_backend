# Fleet Ride — Screen ↔ API map

Traceability between every screen/route in the app and the endpoints in
`fleet_ride.postman_collection.json` (and the Socket.IO `/rt` events). Verified
against `lib/core/router/app_router.dart` and each screen's provider/repository
usage.

**Legend** — 🔵 GET on load · 🟠 write action (POST/PATCH/DELETE) · 🟢 Socket.IO realtime · — no API (static/local/system state).

Endpoint reads are wired through Riverpod read-models (`ui_providers.dart`);
writes call the domain repository directly then invalidate the read-model.

---

## 1 · Entry & Auth

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Splash `01` | `/splash` | — |
| Onboarding `37` | `/onboarding` | — |
| Location priming `35a` | `/permissions/location` | — |
| Notification priming `35b` | `/permissions/notifications` | 🟠 `POST /devices` (when a push token is available) |
| Login / OTP `03` | `/login` | 🟠 `POST /auth/otp/request` · 🟠 `POST /auth/otp/verify` |
| Register `38` `38b` | `/register` | 🟠 `POST /auth/register` |

*(Token refresh `POST /auth/refresh` runs transparently in the HTTP client on 401. `POST /auth/logout` is triggered from the profile hub's Log out; `DELETE /account` from Edit profile — see §5 and §8.)*

---

## 2 · Booking flow

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Route & availability `05` | `/booking/route` | 🔵 `GET /catalog/services` |
| Set pickup `49` | `/booking/set-pickup` | 🔵 `GET /places/suggest` (search) · 🟠 `POST /geocode/reverse` (pin address) |
| Class select `06` | `/booking/class` | 🔵 `GET /catalog/services` · 🔵 `GET /catalog/classes` · 🟠 `POST /routes/estimate` (live distance/ETA) |
| Office select + Request `07` | `/booking/offices` | 🔵 `POST /offices/search` · 🟢 `request_ride` · 🟢 `cancel_ride` |
| Schedule (date/time) `28` `28b` | `/booking/schedule` | 🟢 `schedule_ride` |

---

## 3 · Live trip & in-ride sheets

| Screen (PNG) | Route / sheet | Endpoints |
|---|---|---|
| Live trip: matching→in-ride `09`–`13` | `/trip/live` | 🟢 in: `office_confirmed`, `driver_assigned`, `driver_arriving`/`arrived`, `trip_started`, `meter_tick`, `trip_completed`, `trip_cancelled`, `office_rejected` |
| Driver chat `40` | sheet | 🔵 `GET /trips/{id}/messages` · 🟠 `POST /trips/{id}/messages` · 🟢 `chat_message` (in/out) |
| Add stop `39` | sheet | 🟢 `add_stop` |
| Change route `42` | sheet | 🟢 `change_route` |
| Share trip `41` | sheet | 🟢 `share_trip` |
| Safety `14` | sheet | *(opens SOS)* |
| SOS / emergency `34` | sheet | 🟢 `sos` · 🟢 `share_trip` |

---

## 4 · Post-trip

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Rating `16` | `/trip/rate` | 🟠 `POST /trips/{id}/rating` · 🟢 `submit_rating` |
| Completed / Receipt `15` `17` | `/trip/receipt` | 🔵 `GET /trips/{id}` |
| Lost item `33` | `/lost-item` | 🟠 `POST /trips/{id}/lost-item` |

---

## 5 · Home & trips (shell tabs)

| Screen (PNG) | Location | Endpoints |
|---|---|---|
| Home dashboard `04` | `/` (shell) | 🔵 `GET /me` · 🔵 `GET /catalog/services` |
| Trips history `18` `S7` | `/` trips tab | 🔵 `GET /trips` |
| Profile hub `22` | `/` profile tab | 🔵 `GET /me` · 🔵 `GET /trips` · 🟠 Log out → `DELETE /devices/{token}` + `POST /auth/logout` |
| Arabic home preview `S6` | `/preview/ar-home` | — (standalone RTL preview; no providers — the live Home localizes itself via the `عربي` locale toggle) |

---

## 6 · Marketplace

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Favorites `19` | `/favorites` | 🔵 `GET /me/favorites` · 🟠 `DELETE /me/favorites/{officeId}` |
| Office profile `20` | `/office/:id` | 🔵 `GET /offices/{id}` · 🟠 `POST /me/favorites/{officeId}` |

---

## 7 · Wallet & payments

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Wallet `21` | `/wallet` | 🔵 `GET /wallet` · 🔵 `GET /payment-methods` |
| Top up `29` | `/wallet/topup` | 🟠 `POST /wallet/topup` *(+ `POST /payments/stripe/payment-intent` for 3DS)* |
| Transaction history `29b` | `/wallet/history` | 🔵 `GET /wallet/transactions` |
| Payment methods `27` | `/payments` | 🔵 `GET /payment-methods` · 🟠 `PATCH /payment-methods/{id}` · 🟠 `DELETE /payment-methods/{id}` |
| Add card `28` | `/payments/add-card` | 🟠 `POST /payments/stripe/setup-intent` · 🟠 `POST /payment-methods` |
| Promo codes `29c` | `/promo` | 🔵 `GET /promos` · 🟠 `POST /promos/redeem` |

---

## 8 · Profile settings

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Edit profile `26` | `/profile/edit` | 🔵 `GET /me` · 🟠 `PATCH /me` · 🟠 Delete account → `DELETE /account` |
| Saved places `31` | `/places` | 🔵 `GET /me/places` · 🟠 `DELETE /me/places/{id}` |
| Saved place edit `48` | `/places/edit` | 🟠 `POST /me/places` · 🟠 `PATCH /me/places/{id}` |
| Safety contacts `45` | `/safety-contacts` | 🔵 `GET /me/safety-contacts` · 🟠 `POST` / `DELETE /me/safety-contacts/{id}` · 🟠 `PATCH /me/safety-contacts/auto-share` |
| Privacy `46` | `/privacy` | 🔵🟠 `GET`/`PATCH /me/privacy` |
| Change phone `47` | `/profile/phone` | 🟠 `POST /auth/phone/change` |
| Notifications center `30` | `/notifications` | 🔵 `GET /notifications` · 🟠 `POST /notifications/read-all` *(+ `POST /notifications/{id}/read`)* |

*(Notification prefs `GET`/`PATCH /me/notifications-prefs` are exposed via the profile repository for the settings toggles.)*

---

## 9 · Support

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Support center `23` | `/support` | 🔵 `GET /tickets` · 🔵 `GET /help/articles` *(+ `POST /tickets`)* — ticket rows open the detail screen |
| Ticket detail | `/support/ticket/:id` | 🔵 `GET /tickets/{id}` (subject, status, message thread) |
| Help article `36` | `/help/article/:id` | 🔵 `GET /help/articles/{id}` (live title/category/body; static template on the id-less `/help/article`) |
| Complaint `50` | `/complaint` | 🟠 `POST /complaints` |

---

## 10 · Scheduled & B2B

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| Scheduled details `24a` | `/scheduled/details` | 🟠 `POST /scheduled` |
| Fixed offers `24b` | `/scheduled/offers` | — (static offers) |
| Scheduled status `24c` | `/scheduled/status` | 🔵 `GET /scheduled/{id}` · 🟠 `PATCH` / `DELETE /scheduled/{id}` |
| Corporate `25a` | `/corporate` | 🔵 `GET /corporate/invoices` |
| Corporate invoices `51` | `/corporate/invoices` | 🔵 `GET /corporate/invoices` |
| Family `25b` | `/family` | 🔵 `GET /family/members` |
| Family members `52` | `/family/members` | 🔵 `GET /family/members` · 🟠 `POST` / `PATCH` / `DELETE /family/members/{id}` |

---

## 11 · System states (no API)

| Screen (PNG) | Route | Endpoints |
|---|---|---|
| No offices `S1` | `/state/no-offices` | — |
| Office rejected `S2` | *(live-trip state)* | 🟢 `office_rejected` |
| Payment recovery `S3` | *(top-up/add-card error)* | — |
| Offline `S4` | `/state/offline` | — |
| Location denied `S5` | `/state/location-denied` | — |

---

## Coverage summary

- **68 / 68** collection endpoints are reachable from the app, and **every read has a screen consumer** and every write a trigger — the map above is fully wired, with no orphaned endpoints.
- The four booking reads (`GET /catalog/classes`, `POST /routes/estimate`, `GET /places/suggest`, `POST /geocode/reverse`) are consumed by the class-select and set-pickup screens (local catalog / static values as offline fallback).
- **Logout** (profile hub) fires `DELETE /devices/{token}` then `POST /auth/logout`; **Delete account** (Edit profile) fires `DELETE /account`.
- `GET /help/articles/{id}` powers the help-article body (per-article title/category/body); `GET /tickets/{id}` powers the ticket-detail screen opened from the support center's ticket rows — the last two previously-unwired reads.
- The only endpoint with no dedicated screen is `POST /auth/refresh` — it runs transparently inside the HTTP client on a 401.
- Intentionally static (no backend by design): splash, onboarding, location priming, the fixed-offers list, the system-state screens, and the standalone Arabic home preview.
- Until the backend ships, every endpoint above is served by the in-memory `MockBackend` (contract-shaped), so the whole map runs live against the mock; flip `LIVE=true` to target the real API.
