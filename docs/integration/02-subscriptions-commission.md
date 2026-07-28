# 02 · Subscriptions & Commission (الاشتراكات والعمولة)

> **النموذج الجديد:** الفوترة حسب الدولة (تجربة مجانية + تسجيل ذاتي + Stripe متكرّر) موثّقة في [08](08-subscription-billing.md). هذا الملفّ يبقى مرجع الكتالوج العالميّ + حلّ العمولة الذي يستخدمه التسوية.

يربط `OfficeSubscriptionService` + `CommissionResolver`. خطط الاشتراك **عالميّة**؛ اشتراك المكتب **مُشارد** ويجمّد النِسب لحظة الاشتراك (فلا تتأثّر الرحلات السابقة بتغيير الخطط).

## كتالوج الخطط (عالميّ)
| key | الشهريّ (minor) | عمولة المنصّة | حدّ السائقين |
|---|---|---|---|
| free | 0 | 18% | 5 |
| starter | 20000 | 13% | 25 |
| business | 35000 | 12% | 50 |
| scale | 50000 | 11% | 150 |
| enterprise | مخصّص | مخصّص | — |

```
GET /api/v1/subscription-plans            → قائمة الخطط النشطة
```

## اشتراك المكتب
```
POST /api/v1/offices/{office_id}/subscription
```
Request:
```json
{ "plan_key": "business", "office_rate": 18.0, "currency_code": "USD" }
```
Response `201`:
```json
{ "data": {
  "office_id": 3, "plan_key": "business",
  "fleet_commission_rate": 12.0, "office_commission_rate": 18.0,
  "status": "active"
}}
```
- `fleet_commission_rate` يُجمَّد من الخطّة العالميّة لحظتها.
- اشتراك جديد يُنهي السابق → **اشتراك نشط واحد فقط**.
- حارس: `fleet + office ≤ 100%` وإلّا `422 rate_exceeds_100`.

## حلّ العمولة (داخليّ — يستخدمه التسوية)
`CommissionResolver::forOffice(office_id)` يرجع `{ fleet_rate, office_rate, subscription_plan }`. بلا اشتراك → fallback إلى Free (18% / 0%). تُغذّى مباشرةً في تحرير الرحلة الثلاثيّ ([01](01-wallet-payments.md) + snapshot العمولة).

## سيناريو
مكتب على **business**: رحلة 49.50 → fleet 5.94 (12%) · office 8.91 (18%) · driver 34.65 (الباقي)، مُجمَّدة في `commission_snapshots`. ترقية لـ**scale** لاحقاً لا تغيّر الرحلات السابقة. ✅

> أُثبت في `tests/Feature/Fleet/SubscriptionCommissionTest.php`.
