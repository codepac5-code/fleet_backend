# Fleet Ride — API Reference

> **Generated from `docs/openapi.yaml` — the single source of truth. Do not hand-edit; run `python3 docs/gen_api_md.py`.**
> 61 REST operations · 86 schemas · Socket.IO live-trip channel.

## Conventions

| | |
|---|---|
| Base URL | `https://api.fleetride.qa/v1` |
| Auth | `Authorization: Bearer <accessToken>` (Socket.IO handshake `auth:{token}`) |
| Content type | `application/json; charset=utf-8` |
| **Localization** | Send `Accept-Language: en` \| `ar`. Every response echoes `locale` and localizes all human-facing fields (`message`, `name`, `subtitle`, `description`, `why`…). |
| **Payments** | **Stripe.** Add card = SetupIntent → `pm_…`; pay/top-up = PaymentIntent (3DS/SCA). Raw PAN/CVV never reach Fleet Ride. |
| Money | `number` (QAR, 2dp) — never a formatted string |
| Pagination | `?limit&cursor` → `data.items[]` + `meta.nextCursor` |
| Idempotency | `Idempotency-Key: <uuid>` on request_ride, top-up, payment-intent, add-card |

## The one response envelope — used by EVERY REST endpoint

The endpoint's payload is always under `data`. In the tables below, *Response* `data: X` means the body is this envelope with `data` of type `X`.

```jsonc
{
  "status": boolean,
  "statusCode": integer,
  "message": string,
  "data": object?,
  "error": object?,
  "meta": object?,
  "locale": enum(en|ar),
}
```
_Error uses the same envelope: `status:false`, `statusCode>=400`, `message` localized, `data:null`, `error:{code,field}`._

## Auth

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `POST /auth/otp/request`<br>Send an OTP to a phone number | — | `OtpRequest` | `200` data: `OtpChallenge` |
| `POST /auth/otp/verify`<br>Verify an OTP and issue tokens | — | `OtpVerify` | `200` data: `AuthSession` |
| `POST /auth/register`<br>Create a profile after first verification | — | `RegisterRequest` | `201` data: `AuthSession` |
| `POST /auth/refresh`<br>Exchange a refresh token for a new access token | — | `{refreshToken:string}` | `200` data: `TokenPair` |
| `POST /auth/phone/change`<br>Request an OTP to change the account phone number | — | `OtpRequest` | `200` data: `OtpChallenge` |
| `POST /auth/logout`<br>Revoke the refresh token | — | — | `204` — |
| `DELETE /account`<br>Delete the account and all data | — | — | `204` — |

## Profile

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /me`<br>Full profile (home hydrate) | — | — | `200` data: `User` |
| `PATCH /me`<br>Update name / email / avatar / language | — | `UserPatch` | `200` data: `User` |
| `GET /me/places`<br>List saved places | — | — | `200` data: `SavedPlace[]` |
| `POST /me/places`<br>Add a saved place | — | `SavedPlaceInput` | `201` data: `SavedPlace` |
| `PATCH /me/places/{id}`<br>Update a saved place | `id`* path:string | `SavedPlaceInput` | `200` data: `SavedPlace` |
| `DELETE /me/places/{id}`<br>Remove a saved place | `id`* path:string | — | `204` — |
| `GET /me/safety-contacts`<br>List trusted contacts + auto-share flag | — | — | `200` data: `SafetyContactsResponse` |
| `POST /me/safety-contacts`<br>Add a trusted contact | — | `SafetyContactInput` | `201` data: `SafetyContact` |
| `DELETE /me/safety-contacts/{id}`<br>Remove a trusted contact | `id`* path:string | — | `204` — |
| `PATCH /me/safety-contacts/auto-share`<br>Toggle auto-share of active trips | — | `{enabled:boolean}` | `200` data: `{enabled:boolean}` |
| `GET /me/notifications-prefs`<br> | — | — | `200` data: `NotificationPrefs` |
| `PATCH /me/notifications-prefs`<br> | — | `NotificationPrefs` | `200` data: `NotificationPrefs` |
| `GET /me/privacy`<br> | — | — | `200` data: `PrivacySettings` |
| `PATCH /me/privacy`<br> | — | `PrivacySettings` | `200` data: `PrivacySettings` |

## Marketplace

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `POST /offices/search`<br>Offices that can serve a route (Office-select) | — | `{route:Route}` | `200` data: `OfficeSearchResponse` |
| `GET /offices/{id}`<br>Office profile | `id`* path:string | — | `200` data: `OfficeProfile` |
| `GET /me/favorites`<br> | — | — | `200` data: `FavoriteOffice[]` |
| `POST /me/favorites/{officeId}`<br>Add favorite | `officeId`* path:string | — | `204` — |
| `DELETE /me/favorites/{officeId}`<br>Remove favorite | `officeId`* path:string | — | `204` — |

## Booking

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /places/suggest`<br>Destination search (saved / recent / airport / hotel / search rows) | `q` query:string<br>`lat` query:number<double><br>`lng` query:number<double> | — | `200` data: `{results:PlaceSuggestion[]}` |
| `POST /routes/estimate`<br>Distance, ETA and per-class fares for a pickup→dropoff | — | `RouteEstimateRequest` | `200` data: `RouteEstimate` |
| `GET /catalog/classes`<br>Sub-services (classes) for a service — admin-driven | `service`* query:Service | — | `200` data: `{classes:ClassCard[]}` |
| `POST /geocode/reverse`<br>Map-pin → address | — | `LatLng` | `200` data: `{address:string}` |
| `GET /catalog/services`<br>Admin-configured service catalog (services + sub-services) — DYNAMIC. Drives Home service tiles and class lists. | — | — | `200` data: `ServiceCatalog` |

## Trips

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /trips`<br>Trip history | `status` query:enum(upcoming|completed|cancelled)<br>`limit` query:integer<br>`cursor` query:string | — | `200` data: `TripPage` |
| `GET /trips/{id}`<br>Trip detail / receipt | `id`* path:string | — | `200` data: `Receipt` |
| `POST /trips/{id}/lost-item`<br>Report a lost item | `id`* path:string | `LostItemRequest` | `201` data: `TicketRef` |
| `POST /trips/{id}/rating`<br>Dual rating (driver + office) | `id`* path:string | `RatingRequest` | `200` data: `Ok` |

## Wallet

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /wallet`<br> | — | — | `200` data: `Wallet` |
| `POST /wallet/topup`<br>Top up from a payment method | `Idempotency-Key` header:string<uuid> | `TopUpRequest` | `200` data: `TopUpResult` |
| `GET /wallet/transactions`<br> | `limit` query:integer<br>`cursor` query:string | — | `200` data: `TransactionPage` |

## Payments

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /payment-methods`<br> | — | — | `200` data: `PaymentMethod[]` |
| `POST /payment-methods`<br>Add a card via Stripe PaymentMethod id (pm_…) from a confirmed SetupIntent | `Idempotency-Key` header:string<uuid> | `AddCardRequest` | `201` data: `PaymentMethod` |
| `PATCH /payment-methods/{id}`<br>Set default | `id`* path:string | `{default:boolean}` | `200` data: `PaymentMethod` |
| `DELETE /payment-methods/{id}`<br>Remove a method | `id`* path:string | — | `204` — |
| `GET /promos`<br> | — | — | `200` data: `PromoList` |
| `POST /promos/redeem`<br> | — | `{code:string}` | `200` data: `PromoRedeemResult` |
| `POST /payments/stripe/setup-intent`<br>Create a Stripe SetupIntent to add a card | — | — | `200` data: `StripeSetupIntent` |
| `POST /payments/stripe/payment-intent`<br>Create a Stripe PaymentIntent (top-up or trip; supports 3DS/SCA) | `Idempotency-Key` header:string<uuid> | `StripePaymentIntentRequest` | `200` data: `StripePaymentIntent` |

## Support

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /tickets`<br> | — | — | `200` data: `Ticket[]` |
| `POST /tickets`<br>Open a ticket (lost item / refund / payment / other) | — | `TicketRequest` | `201` data: `TicketRef` |
| `GET /tickets/{id}`<br> | `id`* path:string | — | `200` data: `Ticket` |
| `POST /complaints`<br>Driver / office / safety complaint (safety routes to FleetOS) | — | `ComplaintRequest` | `201` data: `ComplaintResult` |
| `GET /help/articles`<br> | `category` query:string | — | `200` data: `ArticleSummary[]` |
| `GET /help/articles/{id}`<br> | `id`* path:string | — | `200` data: `Article` |

## Scheduled

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `POST /scheduled`<br>Create a scheduled fixed A-to-Z trip | `Idempotency-Key` header:string<uuid> | `ScheduledRequest` | `201` data: `ScheduledTrip` |
| `GET /scheduled/{id}`<br>Scheduled status + assignment timeline | `id`* path:string | — | `200` data: `ScheduledTrip` |
| `PATCH /scheduled/{id}`<br> | `id`* path:string | `ScheduledRequest` | `200` data: `ScheduledTrip` |
| `DELETE /scheduled/{id}`<br>Cancel a scheduled trip | `id`* path:string | — | `204` — |

## B2B

| Endpoint | Parameters | Request body | Response |
|---|---|---|---|
| `GET /corporate/invoices`<br> | — | — | `200` data: `InvoicesResponse` |
| `GET /family/members`<br> | — | — | `200` data: `FamilyMember[]` |
| `POST /family/members`<br> | — | `FamilyMemberInput` | `201` data: `FamilyMember` |
| `PATCH /family/members/{id}`<br> | `id`* path:string | `FamilyMemberInput` | `200` data: `FamilyMember` |
| `DELETE /family/members/{id}`<br>Remove a member | `id`* path:string | — | `204` — |

## Realtime — Socket.IO (`/rt`)

_Raw event frames — **not** wrapped in the ApiResponse envelope._

| Direction | Event | Payload |
|---|---|---|
| client→server | `request_ride` | `SocketIO_RequestRide` |
| client→server | `schedule_ride` | `SocketIO_ScheduleRide` |
| client→server | `cancel_ride` | `SocketIO_CancelRide` |
| client→server | `submit_rating` | `RatingRequest` |
| client→server | `submit_ticket` | `TicketRequest` |
| server→client | `trip_scheduled` | `SocketIO_TripScheduled` |
| server→client | `office_confirmed` | `SocketIO_OfficeConfirmed` |
| server→client | `driver_assigning` | `SocketIO_DriverAssigning` |
| server→client | `driver_assigned` | `SocketIO_DriverAssigned` |
| server→client | `driver_arriving` | `SocketIO_DriverArriving` |
| server→client | `driver_arrived` | `SocketIO_DriverArrived` |
| server→client | `trip_started` | `SocketIO_TripStarted` |
| server→client | `meter_tick` | `SocketIO_MeterTick` |
| server→client | `trip_completed` | `SocketIO_TripCompleted` |
| server→client | `trip_cancelled` | `SocketIO_TripCancelled` |
| server→client | `office_rejected` | `SocketIO_OfficeRejected` |

## Appendix — schemas (attribute : datatype)

_`*` = required · `?` = nullable · `X[]` = array of X._

### AddCardRequest
| attribute | type |
|---|---|
| `stripePaymentMethodId`* | `string` |
| `holderName` | `string?` |
| `setDefault` | `boolean` |

### ApiResponse
| attribute | type |
|---|---|
| `status`* | `boolean` |
| `statusCode`* | `integer` |
| `message`* | `string` |
| `data` | `object?` |
| `error` | `object?` |
| `meta` | `object?` |
| `locale` | `enum(en|ar)` |

### Article
`object`

### ArticleSummary
| attribute | type |
|---|---|
| `id` | `string` |
| `category` | `string` |
| `title` | `string` |
| `readMinutes` | `integer` |

### AuthSession
`object`

### ClassCard
| attribute | type |
|---|---|
| `name` | `string` |
| `subtitle` | `string` |
| `fare` | `Money` |
| `badge` | `string?` |
| `pricing` | `Pricing` |
| `id` | `string?` |

### ClassFare
| attribute | type |
|---|---|
| `className` | `string` |
| `service` | `Service` |
| `pricing` | `Pricing` |
| `fare` | `Money` |
| `subServiceId` | `string?` |

### ComplaintRequest
| attribute | type |
|---|---|
| `about`* | `enum(driver|office|safety|other)` |
| `tripId` | `string?` |
| `description`* | `string` |
| `photoUrl` | `string?` |

### ComplaintResult
| attribute | type |
|---|---|
| `caseId` | `string` |
| `routedTo` | `enum(office|fleetos)` |
| `priority` | `enum(normal|urgent)` |

### Driver
| attribute | type |
|---|---|
| `name`* | `string` |
| `rating`* | `number<float>` |

### Error
| attribute | type |
|---|---|
| `error` | `{code:string, message:string, field:string?}` |

### FamilyMember
| attribute | type |
|---|---|
| `id`* | `string` |
| `name`* | `string` |
| `type`* | `enum(minor|elder|adult)` |
| `approvalRequired` | `boolean` |
| `autoShare` | `boolean` |

### FamilyMemberInput
| attribute | type |
|---|---|
| `name`* | `string` |
| `phone`* | `string` |
| `type`* | `enum(minor|elder|adult)` |
| `approvalRequired` | `boolean` |
| `autoShare` | `boolean` |

### FareBreakdown
| attribute | type |
|---|---|
| `base`* | `Money` |
| `promo` | `Money` |
| `waiting` | `Money` |
| `total`* | `Money` |
| `promoCode` | `string?` |
| `paymentLabel` | `string` |

### FavoriteOffice
| attribute | type |
|---|---|
| `office` | `Office` |
| `responds` | `string` |
| `hours` | `string` |
| `classes` | `string[]` |
| `pricing` | `string` |

### Invoice
| attribute | type |
|---|---|
| `month` | `string` |
| `trips` | `integer` |
| `amount` | `Money` |
| `status` | `enum(unbilled|due|paid)` |

### InvoicesResponse
| attribute | type |
|---|---|
| `current` | `Invoice` |
| `past` | `Invoice[]` |

### LatLng
| attribute | type |
|---|---|
| `lat`* | `number<double>` |
| `lng`* | `number<double>` |
| `label` | `string?` |

### LostItemRequest
| attribute | type |
|---|---|
| `category`* | `enum(Phone|Wallet|Bag|Keys|Other)` |
| `description` | `string` |
| `shareMaskedNumber` | `boolean` |

### Meter
| attribute | type |
|---|---|
| `timeSeconds`* | `integer` |
| `distanceKm`* | `number<double>` |
| `total`* | `Money` |

### Money
`number<double>` — QAR

### NotificationPrefs
| attribute | type |
|---|---|
| `tripUpdates` | `boolean` |
| `promotions` | `boolean` |
| `officeMessages` | `boolean` |
| `safetyAlerts` | `boolean` |

### Office
| attribute | type |
|---|---|
| `id`* | `string` |
| `name`* | `string` |
| `initials`* | `string` |
| `palette`* | `Palette` |
| `rating`* | `number<float>` |
| `verified`* | `boolean` |
| `monitored`* | `boolean` |

### OfficeOffer
| attribute | type |
|---|---|
| `office`* | `Office` |
| `fare`* | `Money` |
| `pricing`* | `Pricing` |
| `etaMinutes`* | `integer` |
| `why` | `string?` |
| `recommended` | `boolean` |

### OfficeProfile
| attribute | type |
|---|---|
| `office` | `Office` |
| `stats` | `{trips:integer, onTimePct:integer, fleetSize:integer}` |
| `classes` | `string[]` |
| `hours` | `string` |
| `responds` | `string` |

### OfficeSearchResponse
| attribute | type |
|---|---|
| `routeKind` | `enum(airport|city)` |
| `offices` | `OfficeOffer[]` |

### Ok
| attribute | type |
|---|---|
| `ok` | `boolean` |

### OtpChallenge
| attribute | type |
|---|---|
| `challengeId`* | `string` |
| `expiresInSec`* | `integer` |
| `length` | `integer` |

### OtpRequest
| attribute | type |
|---|---|
| `dialCode`* | `string` |
| `phone`* | `string` |
| `country` | `string` |

### OtpVerify
| attribute | type |
|---|---|
| `challengeId`* | `string` |
| `code`* | `string` |

### Palette
`enum(a|b|c)`

### PaymentMethod
| attribute | type |
|---|---|
| `id`* | `string` |
| `kind`* | `enum(card|applepay|cash|wallet)` |
| `brand` | `string?` |
| `last4` | `string?` |
| `expiry` | `string?` |
| `default` | `boolean` |
| `stripePaymentMethodId` | `string?` |

### PlaceSuggestion
| attribute | type |
|---|---|
| `type`* | `enum(saved|recent|airport|hotel|search)` |
| `label`* | `string` |
| `sublabel` | `string?` |
| `lat` | `number?` |
| `lng` | `number?` |

### Pricing
`enum(meterEstimate|fixedRoute)`

### PrivacySettings
| attribute | type |
|---|---|
| `locationDuringTrips` | `boolean` |
| `shareTripDataWithOffice` | `boolean` |
| `marketing` | `boolean` |

### Promo
| attribute | type |
|---|---|
| `code`* | `string` |
| `description`* | `string` |
| `state`* | `enum(active|available)` |
| `expiresAt` | `string?` |

### PromoList
| attribute | type |
|---|---|
| `active` | `Promo[]` |
| `available` | `Promo[]` |

### PromoRedeemResult
| attribute | type |
|---|---|
| `ok` | `boolean` |
| `discount` | `string` |

### RatingRequest
| attribute | type |
|---|---|
| `driver`* | `{stars:integer, tags:string[]}` |
| `office`* | `{stars:integer, tags:string[], bookAgain:boolean, favorite:boolean}` |
| `comment` | `string?` |

### Receipt
| attribute | type |
|---|---|
| `tripId`* | `string` |
| `office`* | `Office` |
| `driver` | `Driver` |
| `vehicle` | `Vehicle` |
| `pickup` | `string` |
| `dropoff` | `string` |
| `distanceKm` | `number<double>` |
| `durationSec` | `integer` |
| `pricing` | `enum(Metered|Fixed A-to-Z)` |
| `styleBadge` | `enum(METERED|FIXED A-TO-Z)` |
| `fare`* | `FareBreakdown` |
| `completedAt` | `string<date-time>` |

### RegisterRequest
| attribute | type |
|---|---|
| `challengeId`* | `string` |
| `fullName`* | `string` |
| `email` | `string?` |
| `country` | `string` |

### Route
| attribute | type |
|---|---|
| `pickup`* | `string` |
| `dropoff`* | `string` |
| `distanceKm`* | `number<double>` |
| `etaMinutes`* | `integer` |
| `service`* | `Service` |
| `pricing`* | `Pricing` |
| `className`* | `string` |
| `fare`* | `Money` |
| `subServiceId` | `string?` |

### RouteEstimate
| attribute | type |
|---|---|
| `distanceKm` | `number<double>` |
| `etaMinutes` | `integer` |
| `routeKind` | `enum(airport|city)` |
| `classes` | `ClassFare[]` |

### RouteEstimateRequest
| attribute | type |
|---|---|
| `pickup`* | `LatLng` |
| `dropoff`* | `LatLng` |

### SafetyContact
| attribute | type |
|---|---|
| `id`* | `string` |
| `name`* | `string` |
| `relation` | `string?` |
| `autoShare` | `boolean` |
| `primary` | `boolean` |

### SafetyContactInput
| attribute | type |
|---|---|
| `name`* | `string` |
| `phone`* | `string` |
| `relation` | `string?` |
| `primary` | `boolean` |

### SafetyContactsResponse
| attribute | type |
|---|---|
| `autoShare` | `boolean` |
| `contacts` | `SafetyContact[]` |

### SavedPlace
| attribute | type |
|---|---|
| `id`* | `string` |
| `label`* | `string` |
| `icon`* | `enum(home|work|gem|heart|pin)` |
| `address`* | `string` |
| `lat` | `number?` |
| `lng` | `number?` |

### SavedPlaceInput
| attribute | type |
|---|---|
| `label`* | `string` |
| `icon`* | `enum(home|work|gem|heart|pin)` |
| `address`* | `string` |
| `lat` | `number?` |
| `lng` | `number?` |

### ScheduledRequest
| attribute | type |
|---|---|
| `route`* | `Route` |
| `scheduledFor`* | `string<date-time>` |
| `passengers` | `integer` |
| `luggage` | `integer` |
| `flightNo` | `string?` |
| `officeId` | `string?` |

### ScheduledTrip
| attribute | type |
|---|---|
| `tripId` | `string` |
| `scheduledFor` | `string<date-time>` |
| `office` | `Office` |
| `route` | `Route` |
| `steps` | `{key:enum(booked|office_assigned|driver), state:enum(done|now|pending)}[]` |
| `fare` | `FareBreakdown` |

### Service
`string` — Admin-defined service id — DYNAMIC. Fetched from GET /catalog/services; NOT a fixed enum. New services/sub-services can be added in admin without an app release.

### ServiceCatalog
| attribute | type |
|---|---|
| `version` | `string?` |
| `services` | `ServiceDef[]` |

### ServiceDef
| attribute | type |
|---|---|
| `id`* | `string` |
| `name`* | `string` |
| `subtitle` | `string?` |
| `icon` | `string?` |
| `badge` | `string?` |
| `pricing`* | `Pricing` |
| `active`* | `boolean` |
| `sortOrder` | `integer` |
| `subServices` | `SubService[]` |

### SocketIO_CancelRide
| attribute | type |
|---|---|
| `reason` | `string` |

### SocketIO_DriverArrived
`object` — empty payload

### SocketIO_DriverArriving
| attribute | type |
|---|---|
| `etaMinutes` | `integer` |

### SocketIO_DriverAssigned
| attribute | type |
|---|---|
| `driver`* | `Driver` |
| `vehicle`* | `Vehicle` |
| `etaMinutes`* | `integer` |

### SocketIO_DriverAssigning
`object` — empty payload

### SocketIO_MeterTick
| attribute | type |
|---|---|
| `timeSeconds`* | `integer` |
| `distanceKm`* | `number<double>` |
| `total`* | `Money` |

### SocketIO_OfficeConfirmed
| attribute | type |
|---|---|
| `office` | `Office` |

### SocketIO_OfficeRejected
| attribute | type |
|---|---|
| `reason` | `string` |

### SocketIO_RequestRide
| attribute | type |
|---|---|
| `tripId`* | `string` |
| `route`* | `Route` |
| `office`* | `Office` |

### SocketIO_ScheduleRide
| attribute | type |
|---|---|
| `tripId`* | `string` |
| `route`* | `Route` |
| `scheduledFor`* | `string<date-time>` |

### SocketIO_TripCancelled
| attribute | type |
|---|---|
| `reason` | `string` |

### SocketIO_TripCompleted
| attribute | type |
|---|---|
| `finalFare` | `Money` |

### SocketIO_TripScheduled
| attribute | type |
|---|---|
| `tripId` | `string` |
| `route` | `Route` |
| `scheduledFor` | `string<date-time>` |

### SocketIO_TripStarted
`object` — empty payload

### StripePaymentIntent
| attribute | type |
|---|---|
| `paymentIntentId`* | `string` |
| `clientSecret`* | `string` |
| `status`* | `enum(requires_confirmation|requires_action|processing|succeeded|canceled)` |
| `requiresAction` | `boolean` |
| `amount` | `Money` |

### StripePaymentIntentRequest
| attribute | type |
|---|---|
| `amount`* | `Money` |
| `currency` | `string` |
| `purpose`* | `enum(topup|trip)` |
| `tripId` | `string?` |
| `paymentMethodId` | `string?` |

### StripeSetupIntent
| attribute | type |
|---|---|
| `clientSecret`* | `string` |
| `publishableKey`* | `string` |
| `customerId` | `string?` |

### SubService
| attribute | type |
|---|---|
| `id`* | `string` |
| `serviceId` | `string` |
| `name`* | `string` |
| `subtitle` | `string?` |
| `icon` | `string?` |
| `badge` | `string?` |
| `pricing`* | `Pricing` |
| `baseFare` | `Money?` |
| `active`* | `boolean` |
| `sortOrder` | `integer` |

### Ticket
`object`

### TicketRef
| attribute | type |
|---|---|
| `ticketId`* | `string` |
| `status`* | `enum(open|awaiting_reply|resolved)` |

### TicketRequest
| attribute | type |
|---|---|
| `topic`* | `enum(lost_item|refund|payment|other)` |
| `tripId` | `string?` |
| `message`* | `string` |

### TokenPair
| attribute | type |
|---|---|
| `accessToken`* | `string` |
| `refreshToken`* | `string` |

### TopUpRequest
| attribute | type |
|---|---|
| `amount`* | `Money` |
| `paymentMethodId`* | `string` |

### TopUpResult
| attribute | type |
|---|---|
| `status`* | `enum(succeeded|requires_action|processing)` |
| `balance` | `Money?` |
| `txnId` | `string?` |
| `paymentIntentId` | `string?` |
| `clientSecret` | `string?` |
| `requiresAction` | `boolean` |

### Transaction
| attribute | type |
|---|---|
| `txnId`* | `string` |
| `type`* | `enum(topup|ride|refund|travel)` |
| `label`* | `string` |
| `amount`* | `Money` |
| `credit`* | `boolean` |
| `at`* | `string<date-time>` |

### TransactionPage
| attribute | type |
|---|---|
| `items` | `Transaction[]` |
| `nextCursor` | `string?` |

### TripPage
| attribute | type |
|---|---|
| `items` | `TripSummary[]` |
| `nextCursor` | `string?` |

### TripSummary
| attribute | type |
|---|---|
| `tripId`* | `string` |
| `office`* | `Office` |
| `route`* | `string` |
| `when`* | `string<date-time>` |
| `fare`* | `Money` |
| `status`* | `enum(upcoming|completed|cancelled)` |
| `pricing` | `Pricing` |
| `ratingGiven` | `integer?` |

### User
| attribute | type |
|---|---|
| `id`* | `string` |
| `fullName`* | `string` |
| `phoneMasked`* | `string` |
| `phoneVerified`* | `boolean` |
| `email` | `string?` |
| `avatarUrl` | `string?` |
| `language`* | `enum(en|ar)` |
| `wallet`* | `Wallet` |
| `defaultPaymentId` | `string?` |
| `openTickets` | `integer` |
| `savedPlacesCount` | `integer` |
| `favoriteOfficesCount` | `integer` |

### UserPatch
| attribute | type |
|---|---|
| `fullName` | `string` |
| `email` | `string?` |
| `avatarUrl` | `string?` |
| `language` | `enum(en|ar)` |

### Vehicle
| attribute | type |
|---|---|
| `model`* | `string` |
| `plate`* | `string` |
| `colour`* | `string` |
| `classLabel`* | `string` |

### Wallet
| attribute | type |
|---|---|
| `balance`* | `Money` |
| `currency`* | `string` |
