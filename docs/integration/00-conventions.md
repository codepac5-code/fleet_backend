# 00 · Conventions (الاصطلاحات)

تنطبق على كل نقاط النهاية في هذا المجلّد.

## Base & versioning
```
Base URL:   https://api.fleetos.app
Prefix:     /api/v1
```

## Headers
| Header | إلزاميّ | الوصف |
|---|---|---|
| `Authorization: Bearer <token>` | نعم (ما عدا auth/webhooks) | توكن المستخدم/السائق/المكتب |
| `X-Country: <iso2>` | نعم للموارد المُشاردة | يحدّد الشارد (الدولة) — مثل `qa` |
| `Accept-Language: en \| ar` | لا | لغة الردّ والإشعارات (افتراضيّ `en`) |
| `Idempotency-Key: <uuid>` | نعم للعمليّات المُغيِّرة | تكرار الطلب بنفس المفتاح يرجع نفس النتيجة بلا أثر مزدوج |
| `Content-Type: application/json` | نعم | — |

## المال (Money)
كل المبالغ **أعداد صحيحة بوحدات صغرى** مع رمز العملة:
```json
{ "amount_minor": 4950, "currency_code": "USD" }   // = 49.50
```
النِسب مئويّة عشريّة: `"fleet_rate": 12.0`.

## Idempotency
- العمليّات المُغيِّرة (شحن، حجز، تحرير، استرجاع، قبول رحلة) تتطلّب `Idempotency-Key`.
- إعادة الطلب بنفس المفتاح → نفس الاستجابة، بلا قيد/إسناد مكرّر (مضمون على مستوى الدفتر وقاعدة البيانات).

## Envelope الاستجابة
نجاح:
```json
{ "data": { ... }, "meta": { ... } }
```
خطأ:
```json
{
  "error": {
    "code": "insufficient_funds",
    "message": "Wallet balance is too low.",
    "details": { "required_minor": 4950, "available_minor": 1200 }
  }
}
```

## رموز الأخطاء الشائعة
| code | HTTP | المعنى |
|---|---|---|
| `validation_failed` | 422 | حقول ناقصة/غير صالحة |
| `unauthorized` | 401 | توكن مفقود/غير صالح |
| `forbidden` | 403 | لا صلاحيّة |
| `not_found` | 404 | المورد غير موجود |
| `idempotency_replay` | 200 | طلب مكرّر — تُرجَع النتيجة الأصليّة (ليس خطأً) |
| `insufficient_funds` | 422 | رصيد محفظة غير كافٍ للحجز |
| `already_assigned` | 409 | الرحلة أُسندت لسائق آخر (خسر السباق) |
| `offer_expired` | 409 | عرض الرحلة انتهت صلاحيّته |
| `rate_exceeds_100` | 422 | fleet + office تتجاوز 100% |

## Pagination
```
GET /resource?cursor=<opaque>&limit=20
→ "meta": { "next_cursor": "...", "has_more": true }
```
