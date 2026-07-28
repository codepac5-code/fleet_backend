# Fleet Ride — API Reference (model + migration backed)

> Every attribute below is a **real column** verified against `docs/Models/*.php` (`$fillable`/`$casts`) and `docs/migrations/*.php`. Datatypes are the migration's own types.
> `➕` marks a column added by `migrations/2026_07_15_000001_add_rider_api_missing_columns.php` to close a contract gap.
> The **response envelope, localization rules and Stripe flow are defined once** (below) and apply to every endpoint.

---

## 0. Two backends — which tables the rider API uses

The repo carries two schemas. The rider app targets the **rider schema** (dated `2026-06`/`2026-07`, `global`/tenant connections, **money in integer minor units**). Legacy admin/office tables are only used where no rider table exists.

| Concern | Rider table (used) | Legacy table (not used by app) |
|---|---|---|
| Trips | `ride_bookings` | `bookings` |
| Ratings | `ride_ratings` | `ratings`, `rating_users`, `booking_ratings` |
| Saved cards | `rider_payment_methods` | `payment_methods` (gateway catalog only) |
| Wallet balance | `wallet_balances` (+ `currencies`) | `wallets`, `user_wallets` |
| Wallet ledger | `ledger_payments`, `wallet_transactions` | — |
| Support | `rider_support_tickets` / `rider_support_messages` | `issues` / `replies` |
| Profile extras | `rider_profiles` | — |

---

## 1. Conventions

| | |
|---|---|
| Base URL | `https://fleetapp.net/v1` |
| Auth | `Authorization: Bearer <accessToken>` (Laravel Passport); Socket.IO handshake `auth:{token}` |
| Content type | `application/json; charset=utf-8` |
| Money | **integer minor units** (`*_minor`) + sibling `currency_code` (`currencies.code`, e.g. `QAR`, `decimals=2`). *Legacy tables use `double`; do not mix.* |
| Pagination | `?limit&cursor` →
`data.items[]` + `meta.nextCursor` |
| Idempotency | `Idempotency-Key: <uuid>` → persisted as `ride_bookings.idempotency_key` / `ledger_payments.idempotency_key` (both `unique`) |

---

## 2. The one response envelope — used by EVERY REST endpoint

The payload is always under `data`. Below, *Response `data: X`* means this envelope with `data` of type `X`.

```jsonc
{
  "status":     boolean,          // true on 2xx, false on error
  "statusCode": integer,          // mirrors HTTP status
  "message":    string,           // localized (see §3)
  "data":       object | null,    // the endpoint payload
  "error":      object | null,    // { code:string, message:string, field?:string } on failure
  "meta":       object | null,    // { nextCursor?:string, ... }
  "locale":     "en" | "ar"       // echoes the resolved language
}
```

Errors reuse the same shape: `status:false`, `statusCode>=400`, localized `message`, `data:null`, `error:{code,field?}`.

---

## 3. Localization (applies to every response)

- Request language via `Accept-Language: en | ar`; persisted per user in **`rider_profiles.locale`** `string(5)` (fallback **`users.current_country`** `string(5)`). Update through `PATCH /me`.
- Every response echoes `locale`.
- Bilingual columns exist as **`*_ar` / `*_en` pairs** in the DB; the API returns the single resolved string for the active locale:

| Resource | Arabic column | English column |
|---|---|---|
| Service | `services.title` | `services.title_en` |
| Service desc | `services.description` | `services.description_en` |
| Sub-service | `sub_services.name` | `sub_services.name_en` |
| Sub-service desc | `sub_services.description` | `sub_services.description_en` |
| Help article | `help_suggestions.title` | `help_suggestions.title_en` |
| Help body | `help_suggestions.description` | `help_suggestions.description_en` |
| Country | `countries.name` | `countries.en_name` |
| City | `cities.name` | `cities.en_name` |
| Wallet txn label | `wallet_transactions.description` | `wallet_transactions.description_en` |
| Payment name | `wallet_transactions.paymentName` | `wallet_transactions.paymentName_en` |
| Notification | `app_notifications.title` / `body` (+ `locale` column, `template_key`) | — (row is rendered per `locale`) |

---

## 4. Stripe (payments)

Raw PAN/CVV never reach Fleet Ride. Card data is tokenized by Stripe; the backend stores only ids.

| Stripe object | Column | Table | Notes |
|---|---|---|---|
| Customer `cus_…` | `stripe_customer_id` `string` | `users` | created on first card |
| SetupIntent `seti_…` | `stripe_setup_intent_id` `string` ➕ | `rider_payment_methods` | add-card flow |
| PaymentMethod `pm_…` | `stripe_payment_method_id` `string` ➕ | `rider_payment_methods` | confirmed card |
| PaymentIntent `pi_…` | `stripe_payment_intent_id` `string` ➕ | `ride_bookings` | trip charge (3DS/SCA) |
| PaymentIntent `pi_…` | `provider='stripe'` + `provider_ref` `string(191)` | `ledger_payments` | top-up / generic; idempotency via `idempotency_key` |

Card display columns on `rider_payment_methods`: `type` `string(16)` (`card|applepay|googlepay|cash|wallet`), `brand` `string(24)`, `last4` `string(4)`, `exp` `string(7)`, `is_default` `boolean`.

**Flows** — Add card: `POST /payments/stripe/setup-intent` → confirm client-side → `POST /payment-methods {stripePaymentMethodId}`. Pay/top-up: `POST /payments/stripe/payment-intent` → `requires_action?` 3DS → confirm.

---

## 5. Endpoints

Legend: `*` required · `?` nullable · `➕` column added by the reconciliation migration · type is the **migration** type.

### 5.1 Auth  (`users`, Passport `oauth_*`)

| Endpoint | Request attributes → column : type | Response |
|---|---|---|
| `POST /auth/otp/request` | `dialCode`* → `users.dialCode` string · `phone`* → `users.phoneNumber` string · `country?` → `users.current_country` string(5) | `data: OtpChallenge` |
| `POST /auth/otp/verify` | `challengeId`* string · `code`* string | `data: AuthSession` (Passport token) |
| `POST /auth/register` | `challengeId`* · `fullName`* → `users.firstName`+`users.lastName` string(30) · `email?` → `rider_profiles.email` string · `country?` → `users.current_country` | `data: AuthSession` |
| `POST /auth/refresh` | `refreshToken`* string (`oauth_refresh_tokens`) | `data: TokenPair` |
| `POST /auth/phone/change` | `dialCode`*, `phone`* → `users.phoneNumber` | `data: OtpChallenge` |
| `POST /auth/logout` | — (revokes `oauth_access_tokens`) | `204` |
| `DELETE /account` | — (`users` softDelete `deleted_at`) | `204` |

### 5.2 Profile  (`users` + `rider_profiles`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /me` | see **User** schema §6 | `data: User` |
| `PATCH /me` | `fullName?`→`firstName`/`lastName` · `email?`→`rider_profiles.email` string · `avatarUrl?`→`users.photo` string · `language?`→`rider_profiles.locale` string(5) | `data: User` |
| `GET /me/places` | — | `data: SavedPlace[]` |
| `POST /me/places` | `label`*→`saved_places.label` string(16) · `icon`*→`saved_places.icon` string(16) ➕ · `address`*→`saved_places.address` string ➕ · `lat?`,`lng?`→`decimal(10,7)` | `201 data: SavedPlace` |
| `PATCH /me/places/{id}` | same as POST | `data: SavedPlace` |
| `DELETE /me/places/{id}` | `id`* path | `204` |
| `GET /me/safety-contacts` | — | `data: SafetyContactsResponse` |
| `POST /me/safety-contacts` | `name`*→`safety_contacts.name` · `phone`*→`.phone` · `relation?`→`.relation` string(32) ➕ · `primary?`→`.is_primary` boolean ➕ | `201 data: SafetyContact` |
| `DELETE /me/safety-contacts/{id}` | `id`* path | `204` |
| `PATCH /me/safety-contacts/auto-share` | `enabled`→`safety_contacts.auto_share` boolean | `data: {enabled:boolean}` |
| `GET|PATCH /me/notifications-prefs` | `tripUpdates`,`promotions`,`officeMessages`,`safetyAlerts` → `system_settings.value` json (key `notif_prefs.<userId>`) *(no dedicated table; see Gaps)* | `data: NotificationPrefs` |
| `GET|PATCH /me/privacy` | `locationDuringTrips`,`shareTripDataWithOffice`,`marketing` → `system_settings.value` json *(no dedicated table; see Gaps)* | `data: PrivacySettings` |

### 5.3 Marketplace / Offices  (`offices`, `favorite_offices`, `office_sub_service_prices`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `POST /offices/search` | body `route:Route` (see §6) | `data: OfficeSearchResponse` |
| `GET /offices/{id}` | `id`* path → `offices.id` | `data: OfficeProfile` |
| `GET /me/favorites` | — (`favorite_offices.user_id`) | `data: FavoriteOffice[]` |
| `POST /me/favorites/{officeId}` | `officeId`* → `favorite_offices.office_id` unsignedBigInt | `204` |
| `DELETE /me/favorites/{officeId}` | `officeId`* | `204` |

### 5.4 Booking / Catalog  (`services`, `sub_services`, `saved_places`, `ride_bookings`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /catalog/services` | — | `data: ServiceCatalog` |
| `GET /catalog/classes` | `service`* query → `sub_services.serviceId` | `data: {classes:ClassCard[]}` |
| `GET /places/suggest` | `q` string · `lat`,`lng` double (recent from `ride_bookings`, saved from `saved_places`) | `data: {results:PlaceSuggestion[]}` |
| `POST /routes/estimate` | `pickup`*,`dropoff`* `LatLng` | `data: RouteEstimate` (fares computed from `sub_services.openPrice`/`kmPrice`/`minutePrice` + `office_sub_service_prices`) |
| `POST /geocode/reverse` | `lat`*,`lng`* double | `data: {address:string}` |

### 5.5 Trips  (`ride_bookings`, `ride_ratings`, `lost_items`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /trips` | `status?` query (`ride_bookings.status`) · `limit?`,`cursor?` | `data: TripPage` |
| `GET /trips/{id}` | `id`* → `ride_bookings.id` | `data: Receipt` |
| `POST /trips/{id}/lost-item` | `category`*→`lost_items.category` string(16) ➕ · `description?`→`.description` text ➕ · `shareMaskedNumber?`→`.share_masked_number` boolean ➕ | `201 data: TicketRef` |
| `POST /trips/{id}/rating` | dual → `ride_ratings` rows (driver + office); `stars`→`.stars` tinyint · `tags[]`→`.tags` json ➕ · `comment?`→`.comment` text · `bookAgain?`→`.book_again` boolean ➕ · `favorite?`→`.favorite` boolean ➕ | `data: Ok` |

### 5.6 Wallet  (`wallet_balances`, `currencies`, `wallet_transactions`, `ledger_payments`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /wallet` | `balance`→`wallet_balances.balance` decimal(18,2) · `currency`→`wallet_balances.currency_code` string(10) | `data: Wallet` |
| `POST /wallet/topup` | `amount`*→`ledger_payments.amount_minor` bigint · `paymentMethodId`*→`rider_payment_methods.id`; `Idempotency-Key`→`ledger_payments.idempotency_key` | `data: TopUpResult` (Stripe: `clientSecret`/`requiresAction`) |
| `GET /wallet/transactions` | `limit?`,`cursor?` | `data: TransactionPage` |

### 5.7 Payments  (`rider_payment_methods`, `coupons`, `coupon_users`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /payment-methods` | — → `rider_payment_methods` where `user_id` | `data: PaymentMethod[]` |
| `POST /payment-methods` | `stripePaymentMethodId`*→`.stripe_payment_method_id` string ➕ · `setDefault?`→`.is_default` boolean; `Idempotency-Key` | `201 data: PaymentMethod` |
| `PATCH /payment-methods/{id}` | `default`→`.is_default` boolean | `data: PaymentMethod` |
| `DELETE /payment-methods/{id}` | `id`* path | `204` |
| `GET /promos` | — → `coupons` (+ per-user `coupon_users`) | `data: PromoList` |
| `POST /promos/redeem` | `code`*→`coupons.code` string | `data: PromoRedeemResult` |
| `POST /payments/stripe/setup-intent` | — → `rider_payment_methods.stripe_setup_intent_id` ➕, `users.stripe_customer_id` | `data: StripeSetupIntent` |
| `POST /payments/stripe/payment-intent` | `amount`* minor · `purpose`* `topup|trip` · `tripId?`→`ride_bookings.id` · `paymentMethodId?`; `Idempotency-Key` | `data: StripePaymentIntent` (writes `ride_bookings.stripe_payment_intent_id` ➕ or `ledger_payments.provider_ref`) |

### 5.8 Support  (`rider_support_tickets`, `rider_support_messages`, `complaints`, `help_suggestions`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /tickets` | — → `rider_support_tickets` where `user_id` | `data: Ticket[]` |
| `POST /tickets` | `topic`*→`.topic` string(16) ➕ · `tripId?`→`.booking_id` · `message`*→`rider_support_messages.body` text | `201 data: TicketRef` |
| `GET /tickets/{id}` | `id`* → `rider_support_tickets.id` (+ `rider_support_messages`) | `data: Ticket` |
| `POST /complaints` | `about`*→`complaints.about` string(8) ➕ · `tripId?`→`.booking_id` ➕ · `description`*→`.description` text ➕ · `photoUrl?`→`.photo_url` ➕ | `201 data: ComplaintResult` (`routed_to`,`priority`,`case_ref`) |
| `GET /help/articles` | `category?`→`help_suggestions.category` | `data: ArticleSummary[]` (`readMinutes`→`.read_minutes` ➕) |
| `GET /help/articles/{id}` | `id`* → `help_suggestions.id` | `data: Article` |

### 5.9 Scheduled  (`ride_bookings` where `scheduled_at` not null)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `POST /scheduled` | `route`*:Route · `scheduledFor`*→`ride_bookings.scheduled_at` timestamp · `passengers?`→`.passengers` smallint · `luggage?`→`.luggage` smallint · `flightNo?`→`.flight_no` string(16); `Idempotency-Key` | `201 data: ScheduledTrip` |
| `GET /scheduled/{id}` | `id`* → `ride_bookings.id` | `data: ScheduledTrip` (steps from `dispatch_jobs`/`dispatch_offers`) |
| `PATCH /scheduled/{id}` | as POST (bumps `ride_bookings.change_revision`) | `data: ScheduledTrip` |
| `DELETE /scheduled/{id}` | `id`* (sets `cancelled_at`,`cancel_reason`) | `204` |

### 5.10 B2B  (`corporate_invoices`, `family_members`)

| Endpoint | Attributes → column : type | Response |
|---|---|---|
| `GET /corporate/invoices` | — → `corporate_invoices` where `user_id` ➕ | `data: InvoicesResponse` |
| `GET /family/members` | — → `family_members` where `user_id` ➕ | `data: FamilyMember[]` |
| `POST /family/members` | `name`*→`.name` · `phone`*→`.phone` · `type`*→`.type` string(8) ➕ · `approvalRequired?`→`.approval_required` boolean ➕ · `autoShare?`→`.auto_share` boolean ➕ | `201 data: FamilyMember` |
| `PATCH /family/members/{id}` | as POST | `data: FamilyMember` |
| `DELETE /family/members/{id}` | `id`* | `204` |

### 5.11 Realtime — Socket.IO (`/rt`)  — raw frames, **not** enveloped

Live-trip state = `ride_bookings.status`; dispatch = `dispatch_jobs`/`dispatch_offers`; in-ride chat = `booking_chat_messages`; meter = computed from `distance_m`/`duration_s`.

| Dir | Event | Backed by |
|---|---|---|
| c→s | `request_ride` | insert `ride_bookings` (`status='matching'`) |
| c→s | `schedule_ride` | insert `ride_bookings` (`scheduled_at`) |
| c→s | `cancel_ride` | `ride_bookings.cancelled_at`,`cancel_reason` |
| c→s | `submit_rating` | `ride_ratings` |
| s→c | `office_confirmed` | `ride_bookings.office_id`, `dispatch_jobs` |
| s→c | `driver_assigned` | `ride_bookings.driver_id` ➕, `vehicle_id` ➕, `driver_presence` |
| s→c | `driver_arriving`/`arrived` | `driver_presence.lat/lng/status` |
| s→c | `trip_started` / `meter_tick` | `ride_bookings.distance_m`,`duration_s` |
| s→c | `trip_completed` | `ride_bookings.completed_at`,`total_minor` |
| s→c | `trip_cancelled` / `office_rejected` | `ride_bookings.cancel_reason` / `dispatch_offers.status` |

---

## 6. Schemas → real columns (attribute : type)

`*` required · `?` nullable · `➕` added by reconciliation migration.

### User  ← `users` (+ `rider_profiles`, `wallet_balances`)
| attribute | column : type |
|---|---|
| `id`* | `users.id` bigint |
| `firstName`* | `users.firstName` string(30) |
| `lastName`* | `users.lastName` string(30) |
| `phoneNumber`* | `users.phoneNumber` string (unique) |
| `dialCode`* | `users.dialCode` string |
| `gender?` | `users.gender` string(10) `male\|female` |
| `photo?` | `users.photo` string |
| `isActive` | `users.isActive` boolean |
| `referralCode?` | `users.referralCode` string |
| `walletBalance` | `users.walletBalance` double *(legacy)* / `wallet_balances.balance` decimal(18,2) *(authoritative)* |
| `stripeCustomerId?` | `users.stripe_customer_id` string |
| `current_country?` | `users.current_country` string(5) |
| `email?` | `rider_profiles.email` string |
| `locale` | `rider_profiles.locale` string(5) |

### SavedPlace ← `saved_places`
`id` bigint · `user_id` bigint · `label` string(16) · `icon` string(16) ➕ · `title` string · `address` string ➕ · `lat` decimal(10,7) · `lng` decimal(10,7)

### SafetyContact ← `safety_contacts`
`id` · `user_id` · `name` string · `phone` string · `relation` string(32) ➕ · `is_primary` boolean ➕ · `auto_share` boolean

### Office ← `offices`
`id` bigint · `officeName` string · `initials` string(4) ➕ · `palette` string(1) ➕ (`a\|b\|c`) · `logo?` string · `rating` float · `ratings_count` int ➕ · `is_verified` boolean ➕ · `is_monitored` boolean ➕ · `on_time_percentage` decimal(5,2) ➕ · `avg_response_minutes?` int ➕ · `contactNumber?` string(20) · `country`/`city`/`region` string · `address?` text · `lat`/`lng` decimal(10,7) ➕ · `working_hours?` json ➕
Rating buckets: `ratingExcellent`…`ratingPoor` int.

### ServiceDef ← `services`
`id` bigint · `title`/`title_en` string · `description`/`description_en?` string · `image` string · `icon?` string ➕ · `badge?` string ➕ · `sort_order` int ➕ · `status` boolean (→ `active`) · `travel_service` boolean

### SubService (ClassCard/ClassFare) ← `sub_services`
`id` bigint · `serviceId` bigint · `name`/`name_en` string · `description`/`description_en?` text · `image?`/`icon?` string ➕ · `badge?` string ➕ · `sort_order` int ➕ · `status` boolean · `openPrice`/`kmPrice`/`minutePrice` decimal(10,2) · `base_fare?` decimal(10,2) ➕ · `is_travel` boolean
Per-office override: `office_sub_service_prices.{openPrice,kmPrice,minutePrice}` decimal(10,2).

### RideBooking (TripSummary / Receipt / ScheduledTrip) ← `ride_bookings`
| attribute | column : type |
|---|---|
| `id`* | `id` bigint |
| `user_id`* | `user_id` bigint |
| `office_id` | `office_id` bigint |
| `driver_id?` | `driver_id` bigint ➕ |
| `vehicle_id?` | `vehicle_id` bigint ➕ |
| `service` | `service` string(16) |
| `service_class` | `service_class` string(32) |
| `pricing_style` | `pricing_style` string(16) (`meterEstimate\|fixedRoute`) |
| `status` | `status` string(16) |
| pickup | `pickup_lat`/`pickup_lng` decimal(10,7), `pickup_title?`,`pickup_note?` string |
| dropoff | `dropoff_lat`/`dropoff_lng` decimal(10,7), `dropoff_title?` string |
| `distance_m` | unsignedInt · `duration_s` unsignedInt |
| `currency_code` | string(3) |
| `fare_minor` | int · `discount_minor` int · `waiting_minor` int ➕ · `tip_minor` int ➕ · `total_minor` int · `held_minor` int |
| `payment_method` | string(16) |
| `stripe_payment_intent_id?` | string ➕ |
| `promo_code?` | string · `coupon_id?` bigint ➕ |
| `scheduled_at?` | timestamp · `passengers?`,`luggage?` smallint · `flight_no?` string(16) |
| `assigned_at?`/`completed_at?`/`cancelled_at?`/`rated_at?`➕ | timestamp · `cancel_reason?` string |
| `change_revision` | unsignedInt · `source` string(16) · `created_by?` string(40) |

### RideRating (RatingRequest) ← `ride_ratings`
`id` · `booking_id` bigint · `rater_type`/`rater_id` · `ratee_type`/`ratee_id` (two rows: driver + office) · `stars` tinyint · `tags` json ➕ · `comment?` text · `book_again?` boolean ➕ · `favorite?` boolean ➕

### PaymentMethod ← `rider_payment_methods`
`id` · `user_id` · `type` string(16) (`card\|applepay\|googlepay\|cash\|wallet`) · `brand?` string(24) · `last4?` string(4) · `exp?` string(7) · `is_default` boolean · `stripe_payment_method_id?` string ➕ · `stripe_setup_intent_id?` string ➕

### Wallet ← `wallet_balances` / Transaction ← `wallet_transactions`
Wallet: `balance` decimal(18,2) · `currency_code` string(10) · (currency meta from `currencies`: `code`,`symbol`,`decimals`,`exchange_rate`).
Transaction: `id` · `amount` double · `balance_before?`/`balance_after?` double · `status` string(50) · `transaction_type?` string · `transaction_reference?` string(100) · label via `description`/`description_en`, `paymentName`/`paymentName_en`.

### Promo ← `coupons` (+ `coupon_users`)
`code?` string · `discountType?` string (`percentage\|fixed`) · `discount?` double · `isPercentage` boolean · `isActive` boolean · `limit` int · `expireDate?` datetime · per-user `coupon_users.count` int.

### Ticket ← `rider_support_tickets` / message ← `rider_support_messages`
Ticket: `id` · `user_id` · `booking_id?` (→`tripId`) · `office_id?` · `category` string(32) · `topic` string(16) ➕ · `layer` string(16) · `subject` string · `status` string(16) (`open\|awaiting_reply\|resolved`) · `last_message_at?` timestamp.
Message: `id` · `ticket_id` · `sender_type` string(8) · `sender_id` · `body` text · `created_at`.

### Complaint ← `complaints` ➕(table)
`id` · `user_id` · `booking_id?` (→`tripId`) · `about` string(8) (`driver\|office\|safety\|other`) · `description` text · `photo_url?` string · `routed_to` string(8) (`office\|fleetos`) · `priority` string(8) (`normal\|urgent`) · `case_ref?` string · `status` string(16)

### LostItem ← `lost_items` ➕(table)
`id` · `user_id` · `booking_id` (→`tripId`) · `ticket_id?` · `category` string(16) (`Phone\|Wallet\|Bag\|Keys\|Other`) · `description?` text · `share_masked_number` boolean · `status` string(16)

### FamilyMember ← `family_members` ➕(table)
`id` · `user_id` · `name` string · `phone` string · `type` string(8) (`minor\|elder\|adult`) · `approval_required` boolean · `auto_share` boolean

### CorporateInvoice (InvoicesResponse) ← `corporate_invoices` ➕(table)
`id` · `user_id` · `month` string(7) `YYYY-MM` · `trips` unsignedInt · `amount_minor` int · `currency_code` string(3) · `status` string(16) (`unbilled\|due\|paid`)

### Article ← `help_suggestions`
`id` · `category?` string · `title`/`title_en` string · body `description`/`description_en?` text · `read_minutes?` unsignedInt ➕ · `priority` int · `target_user` (`user\|driver\|web`)

### ArticleSummary ← `help_suggestions`
`id` · `category?` · `title` (localized) · `readMinutes` → `read_minutes` ➕

### AppNotification ← `app_notifications`
`id` · `notifiable_type`/`notifiable_id` · `template_key?` string(64) · `type` string(64) · `locale` string(8) · `title?` string(191) · `body?` text · `data?` json · `read_at?` timestamp

### DeviceToken ← `app_device_tokens`
`id` · `owner_type`/`owner_id` · `token` string(255) unique · `platform?` string(16) · `last_seen_at?` timestamp

---

## 7. Reconciliation notes

**Columns added** by `2026_07_15_000001_add_rider_api_missing_columns.php` (all `➕` above), plus new tables `family_members`, `complaints`, `lost_items`, `corporate_invoices` with matching models. Model `$fillable`/`$casts` updated for: `SavedPlace`, `SafetyContact`, `RiderPaymentMethod`, `RideBooking`, `RideRating`, `RiderSupportTicket`, `HelpSuggestion`, `Office`.

**Still schema-less (served from `system_settings.value` json, not a typed table)** — `NotificationPrefs`, `PrivacySettings`. If you want typed columns, add a `rider_preferences` table (booleans: `trip_updates`, `promotions`, `office_messages`, `safety_alerts`, `location_during_trips`, `share_trip_data_with_office`, `marketing`) — say the word and I'll generate it.

**Pre-existing mismatches to fix in the legacy schema** (not rider-critical, flagged for hygiene): `User::$fillable` lists `officeId` (no column); `Office` fillable `driverDues`/`displayName`/`profileImage` (columns are `driversDues`, none); `OfficeWallet` cast key `offceId` typo; `DrieverWallet::$table='driver_wallets'` vs migration `driever_wallets`; `WalletTransaction` `transaction_type` not fillable; `Vehicle::vehicleBrand()`/`lastDriver()` reference non-existent columns; `help_suggestions.description_en` now added to fillable.
