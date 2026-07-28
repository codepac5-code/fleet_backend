# 06 · Marketplace Modules (الوحدات)

نقاط `api/v1` (بادئة `/api/v1`، رأس `X-Country`، حراسة Passport). الظرف والاصطلاحات في [00](00-conventions.md).

## المكاتب المفضّلة (الراكب)
```
GET    favorites/offices               → { "data": { "office_ids": [9,3] } }
POST   favorites/offices/{office}      → 201 { "office_id": 9, "favorite": true }
DELETE favorites/offices/{office}      → { "office_id": 9, "favorite": false }
```
مُشارد لكل دولة (مفضّلات الراكب تختلف بين الدول). الإضافة idempotent.

## المحادثة الحيّة (الراكب ↔ المكتب)
```
GET  chat/conversations                          → قائمة محادثات الراكب
POST chat/conversations                          { "office_id": 3, "booking_id": 5001? } → 201
GET  chat/conversations/{id}/messages?before_id= → الرسائل (تصاعديّاً)
POST chat/conversations/{id}/messages            { "body": "..." } → 201
POST chat/conversations/{id}/read                → تعليم رسائل الطرف الآخر مقروءة
```
عند إرسال الراكب رسالةً يُبثّ حدث `chat.message_created` على قناة `office.{id}` (والعكس على `user.{id}`) عبر نفس outbox/relay/gateway في [04](04-realtime-notifications.md). ملكيّة المحادثة مفروضة (الراكب يرى محادثاته فقط).

## التقييم الثنائيّ (راكب ↔ سائق)
```
POST api/v1/bookings/{booking}/rating          { "stars": 1..5, "comment": ? }   ← الراكب يقيّم السائق
POST api/v1/driver/bookings/{booking}/rating   { "stars": 1..5, "comment": ? }   ← السائق يقيّم الراكب
GET  api/v1/drivers/{driver}/rating            → { count, average }
```
الطرف المُقيَّم يُشتقّ **خادميّاً** (السائق من إسناد الـdispatch، الراكب من الحجز) لا من الطلب. تقييم واحد لكل اتّجاه لكل حجز (لا تكرار/تعديل)، ممنوع تقييم النفس، `stars` بين 1 و5. لوحة المكتب/المشرف تقرأ الملخّص عبر نفس الخدمة.

## التقارير (لوحة المكتب/المشرف — خدمة داخليّة)
`App\Http\Core\Classes\Report\ReportService` فوق `commission_snapshots` + الدفتر:
- `officeSummary(officeId, currency)` · `fleetSummary(currency)` · `driverEarnings(driverId, currency)`
- يعيد: عدد الرحلات · الإجماليّ · عمولة المنصّة · حصّة المكتب · حصّة السائق · رصيد الإيراد من الدفتر.

## التدقيق (داخليّ)
`App\Http\Core\Classes\Audit\AuditLogService::record(action, actorType?, actorId?, subjectType?, subjectId?, meta?, ip?)` — سجلّ إلحاقيّ مُشارد؛ قراءة `forSubject` / `forActor`.

> مُثبَتة في `tests/Feature/Fleet/{FavoriteOfficeTest,ChatTest,ReportTest,AuditLogTest}.php`.
