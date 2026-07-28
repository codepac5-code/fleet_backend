# 07 · Panel (Office / Admin) — لوحة التحكّم

نقاط لوحة التحكّم (حراسة **session**: `auth:office` / `auth:admin`، مع `panel.country-db` + صلاحيّات Spatie). تكمّل الطرف الإداريّ للنواة المختبَرة. ترجع JSON بشكل `{ "data": ... }`.

## الاشتراكات (المشرف يُسند خطّة لمكتب)
```
GET panel/admin/offices/{office}/subscription   (صلاحيّة: view office list)
PUT panel/admin/offices/{office}/subscription   (صلاحيّة: update office)
    { "plan_key": "business", "office_rate": 18.0, "currency_code": "USD", "fleet_rate": 12.0? }
GET panel/office/subscription                    ← المكتب يرى اشتراكه النشط
```
يُجمَّد `fleet_commission_rate` من الخطّة لحظتها؛ اشتراك جديد يُنهي السابق؛ حارس `fleet+office ≤ 100%` ([02](02-subscriptions-commission.md)).

## التقارير (فوق الدفتر + commission_snapshots)
```
GET panel/office/reports/summary?currency_code=USD   → ملخّص المكتب (المُصادَق)
GET panel/admin/reports/fleet?currency_code=USD      → ملخّص المنصّة الكامل
```
يعيد: عدد الرحلات · الإجماليّ · عمولة المنصّة · حصّة المكتب · حصّة السائق · رصيد الإيراد من الدفتر.

## تسوية الرحلة المكتملة (المكتب)
```
POST panel/office/bookings/{booking}/settle   (صلاحيّة: edit order status)
     { "total_minor": 4950, "payment_method": "digital|cash", "currency_code": "USD"?, "fare_minor": ?, "discount_minor": ? }
```
يأخذ **السائق من إسناد الـdispatch الفعليّ** (`assigned_driver_id`، غير قابل للتزوير) لا من الطلب؛ ويتطلّب وظيفة إسناد بحالة `assigned` للمكتب وإلّا `422 ride_not_settleable`. ثمّ:
- **digital** → يحرّر من الضمان: السائق + إيراد المكتب + إيراد المنصّة (تقسيم ثلاثيّ) عبر `RideLifecycleService::settle`.
- **cash** → عمولة المنصّة+المكتب تُقيَّد على ذمم السائق (dues).
- يبثّ `ride.released` على booking·driver·office **ذرّيّاً** مع التسوية (transactional outbox).
- **مانع للتكرار**: إعادة التسوية لا تكرّر القيد ولا الحدث (لقطة العمولة هي العلامة).

## المحادثة (طرف المكتب)
```
GET  panel/office/chat                          → محادثات المكتب (الأحدث أولاً)
GET  panel/office/chat/{conversation}/messages
POST panel/office/chat/{conversation}/messages  { "body": "..." }   ← يُرسَل كـ sender=office
POST panel/office/chat/{conversation}/read
```
الملكيّة مفروضة (المكتب يرى محادثاته فقط). إرسال المكتب يبثّ `chat.message_created` على قناة `user.{id}` ([06](06-marketplace-modules.md)).

> الخدمات مُثبَتة في `tests/Feature/Fleet/{SubscriptionCommissionTest,ReportTest,ChatTest}.php`. تحكّمات اللوحة رقيقة فوقها (نمط اللوحة: invokable + حقن الخدمة في `__invoke`).
