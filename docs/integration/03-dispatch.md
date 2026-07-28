# 03 · Dispatch (الإسناد)

يربط `DispatchService`. نموذج السوق: الإسناد **ضمن نطاق المكتب** عبر عروض متتابعة + **قبول ذرّيّ** (أوّل سائق يقبل يفوز).

---

## 1) نبضة حضور السائق — Driver heartbeat (DriverX)
```
POST /api/v1/driver/presence
```
Request:
```json
{ "office_id": 3, "status": "online", "lat": 25.2871, "lng": 51.5310 }
```
`status`: `online | busy | offline`. تُرسَل دوريّاً (heartbeat). الحضور القديم يُستبعَد من البحث.
Response `200`:
```json
{ "data": { "driver_id": 9, "status": "online", "heartbeat_at": "2026-06-25T09:40:00Z" }}
```

---

## 2) إنشاء وظيفة إسناد — Create dispatch job (عند تأكيد الراكب للمكتب)
```
POST /api/v1/dispatch/jobs
Idempotency-Key: <booking-scoped>
```
Request:
```json
{ "booking_id": 5001, "office_id": 3, "service_class": "standard", "lat": 25.2854, "lng": 51.5310 }
```
Response `201` — يُطلق **موجة العروض الأولى تلقائيّاً** على أقرب السائقين المتاحين:
```json
{ "data": { "booking_id": 5001, "office_id": 3, "status": "offered", "wave": 1,
  "offers": [ { "driver_id": 101, "distance_m": 300, "expires_at": "..." } ] }}
```
إن لم يوجد سائق متاح: `status: "pending"`, `offers: []`.

---

## 3) إطلاق موجة عروض يدويّاً — Offer a wave (لوحة المكتب)
يختار أقرب المرشّحين المؤهّلين ويرسل لهم عرضاً مؤقّتاً.
```
POST /panel/office/bookings/{booking_id}/offer
```
Request (اختياريّ، له افتراضات):
```json
{ "ttl_seconds": 20, "radius_meters": 5000, "limit": 5 }
```
Response `200`:
```json
{ "data": {
  "wave": 1, "status": "offered",
  "offers": [
    { "driver_id": 101, "distance_m": 300, "expires_at": "2026-06-25T09:41:20Z" },
    { "driver_id": 102, "distance_m": 900, "expires_at": "2026-06-25T09:41:20Z" }
  ]
}}
```
الموجة التالية تتخطّى المرفوضين والمشغولين والمعروض عليهم سابقاً تلقائيّاً.

**تقدّم الموجات (تلقائيّ):** أمر `php artisan fleet:dispatch-tick --daemon` (تحت supervisor) يُنهي العروض المنتهية ثمّ يُطلق الموجة التالية لكل وظيفة غير مُسنَدة بلا عرض نشط — لكل شارد. يتوقّف عند الإسناد أو نفاد المرشّحين (`exhausted`).

---

## 4) قبول/رفض السائق — Accept / Reject (DriverX)
```
POST /api/v1/driver/offers/{booking_id}/accept
POST /api/v1/driver/offers/{booking_id}/reject
```
قبول ناجح `200`:
```json
{ "data": { "booking_id": 5001, "assigned_driver_id": 9, "status": "assigned" }}
```
قبول خاسر (سبقه آخر) `409`:
```json
{ "error": { "code": "already_assigned", "message": "This ride was just assigned to another driver." }}
```
عرض منتهٍ `409`: `offer_expired`.

عند الإسناد يُبثّ حدث `dispatch.ride_assigned` (انظر [04](04-realtime-notifications.md)) ذرّيّاً مع الإسناد.

---

## المرشّحون (للوحة المكتب)
```
GET /api/v1/dispatch/candidates?office_id=3&lat=..&lng=..&radius_meters=5000&limit=10
```
Response: قائمة سائقين online داخل المكتب والنطاق، مرتّبة بالأقرب، مع `distance_m`.

---

## سيناريوهات
**أ) قبول متزامن:** 3 سائقين يضغطون "قبول" لنفس الرحلة في نفس اللحظة → **واحد فقط** يحصل على `assigned`، الباقي `409 already_assigned`. مستحيل الإسناد المزدوج (تحديث شرطيّ ذرّيّ على `assigned_driver_id IS NULL`). ✅

**ب) تقدّم الموجات:** أقرب سائق يرفض → الموجة التالية تنتقل للسائق التالي، ولا تعيد عرض المرفوض.

**ج) الفائز يصير busy:** السائق الفائز يُستبعَد تلقائيّاً من رحلات جديدة حتى يعود online.

**د) انتهاء صلاحيّة:** عرض تجاوز `ttl` → لا يُقبَل (`offer_expired`)، وتُطلَق موجة جديدة.

> أُثبتت كلّها في `tests/Feature/Fleet/DispatchTest.php`.
