# 01 · Wallet & Payments (المحفظة والمدفوعات)

يربط `PaymentService` + `FleetWalletService`. كل تغيير مالٍ يمرّ بدفتر قيد مزدوج ذرّيّ. البوّابات: `stripe · mtn · syriatel` (محايد — تُضاف غيرها بلا تغيير العقد).

---

## 1) إنشاء نيّة شحن — Create top-up intent
يُنشئ نيّة دفع (pending) ويعيد ما يلزم لبدء الدفع لدى البوّابة.

```
POST /api/v1/wallet/topups
Idempotency-Key: 0c1f...   (required)
```
Request:
```json
{ "amount_minor": 10000, "currency_code": "USD", "provider": "stripe" }
```
Response `201`:
```json
{ "data": {
  "uuid": "9b2e...", "status": "pending",
  "amount_minor": 10000, "currency_code": "USD",
  "provider": "stripe", "idempotency_key": "0c1f..."
}}
```
> التطبيق يكمل الدفع لدى البوّابة بالـclient secret/redirect (خاص بكل بوّابة). تأكيد القيد يحدث عبر الـwebhook فقط.

---

## 2) Webhook البوّابة — Gateway webhook (server-to-server)
نقطة واحدة محايدة. **مانعة للتكرار**: تكرار التسليم لا يشحن مرّتين.

```
POST /api/v1/payments/webhook/{provider}
```
Request (مُطبَّع بعد التحقّق من توقيع البوّابة):
```json
{ "idempotency_key": "0c1f...", "status": "succeeded", "provider_ref": "ch_abc123" }
```
Response `200`:
```json
{ "data": { "uuid": "9b2e...", "status": "succeeded", "ledger_transaction_uuid": "f3a1..." }}
```
الحالات: `succeeded` → يشحن المحفظة مرّة واحدة بالضبط · `failed` → تُعلَّم فاشلة بلا شحن.

---

## 3) رصيد المحفظة — Wallet balance
```
GET /api/v1/wallet/balance?currency_code=USD
```
Response `200`:
```json
{ "data": { "owner_type": "user", "owner_id": 7, "currency_code": "USD", "balance_minor": 10000 }}
```

---

## 4) استرجاع رحلة — Refund booking
```
POST /api/v1/bookings/{booking_id}/refund
Idempotency-Key: rf-...   (required)
```
Request:
```json
{ "amount_minor": 5000, "currency_code": "USD", "from_escrow": true, "provider": "stripe" }
```
Response `200`:
```json
{ "data": { "uuid": "...", "kind": "refund", "status": "refunded", "ledger_transaction_uuid": "..." }}
```
`from_escrow=true` يرجع من حجز الرحلة (قبل التحرير)؛ `false` يرجع من إيراد المنصّة (بعد التحرير).

---

## 5) السحوبات — Payouts (خروج المال)
السائق يسحب من محفظته، المكتب من إيراده؛ المشرف يعتمد/يدفع.
```
POST /api/v1/driver/payouts        { "amount_minor": 5000, "currency_code": "USD" }  → 201 pending
GET  /api/v1/driver/payouts        → قائمة طلبات السائق
POST /panel/office/payouts         { "amount_minor": 5000 }   (المكتب من الإيراد)
GET  /panel/office/payouts
GET  /panel/admin/payouts          → الطلبات المعلّقة (كل المُلّاك)
POST /panel/admin/payouts/{id}/pay     → يخصم من المصدر ويقيّد payout_clearing (idempotent)
POST /panel/admin/payouts/{id}/reject  { "note": "..." }
```
الطلب يتحقّق أنّ الرصيد المتاح ≥ المبلغ وإلّا `422 insufficient_funds`. الدفع يعيد التحقّق ثمّ يقيّد الدفتر مرّة واحدة بالضبط (`payout:{id}`)؛ إعادة الدفع لا تكرّر.

---

## سيناريوهات
**أ) شحن ناجح + تسليم webhook مكرّر:**
1. `POST /wallet/topups` → pending.
2. webhook `succeeded` → الرصيد 10000.
3. نفس الـwebhook يصل ثانيةً (إعادة محاولة البوّابة) → الرصيد يبقى 10000 (قيد topup واحد). ✅

**ب) فشل الدفع:** webhook `failed` → الحالة failed، الرصيد بلا تغيير.

**ج) استرجاع مزدوج بنفس المفتاح:** نداءان لـ`/refund` بنفس `Idempotency-Key` → استرجاع واحد فقط في الدفتر. ✅

> أُثبتت كلّها في `tests/Feature/Fleet/PaymentWebhookTest.php`.
