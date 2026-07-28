# 08 · Subscription Billing (فوترة الاشتراك — النموذج الجديد)

نموذج فوترة **حسب الدولة**: كل دولة إمّا `commission` (عمولة فقط، بلا اشتراك — مثل سوريا) أو `subscription` (تسجيل ذاتي + تجربة مجانية + فوترة Stripe متكرّرة — مثل أمريكا). النموذج **إضافيّ (shadow)**: الافتراضي `commission` فيبقى السلوك القديم كما هو، ومسار التسوية الثلاثيّ ([01](01-wallet-payments.md)/[02](02-subscriptions-commission.md)) لم يتغيّر — فقط توسّع مصدر النِسب.

> يكمّل [02](02-subscriptions-commission.md) (الكتالوج العالميّ + حلّ العمولة). أُثبت في `OfficeSubscriptionLifecycleTest` · `SubscriptionWebhookTest` (260 اختباراً أخضر).

## 1) وضع الفوترة للدولة
- يُخزَّن في `infrastructure_nodes.billing_mode` (عالميّ، افتراضي `commission`).
- يُقرأ عبر `RegionBilling::mode($node)` / `isSubscription()` / `isCommission()` (null-safe → commission).
- **تبديل من اللوحة:** أدمن → «وضع فوترة الدول» (`panel.admin.regions.billing`) → قائمة اختيار لكل دولة.

## 2) دورة حياة الاشتراك
جدول `office_subscriptions` (مُشارد) — أعمدة الحياة المضافة: `trial_ends_at` · `current_period_end` · `cancel_at_period_end` · `provider` · `provider_customer_id` · `provider_subscription_id`.

| status | يمنح الوصول والنِسب؟ | المعنى |
|---|---|---|
| `trialing` | ✅ | تجربة مجانية سارية |
| `active` | ✅ | مدفوع ونشط |
| `past_due` | ✅ | فشل تجديد — **يبقى فعّالاً** + تنبيه الأدمن (لا تعليق تلقائي) |
| `canceled` / `ended` | ❌ | يعود لنِسب Free (18/0) |

- **التجربة قابلة للضبط لكل خطة**: `subscription_plans.trial_days` (افتراضي 14).
- `CommissionResolver` يستخدم `currentFor()` (يشمل `trialing/active/past_due`) → المكتب في التجربة يأخذ نِسب خطته فوراً.
- **أمريكا = اشتراك + عمولة مخفّضة** (تبقى نِسب الخطط: starter 13% / business 12% / scale 11%).

## 3) التسجيل الذاتي (عام)
```
GET  /office/register        → نموذج ثنائي اللغة (يعرض الدول ذات وضع الاشتراك فقط)
POST /office/register        → إنشاء الحساب + دخول + توجيه لصفحة الاشتراك
```
التدفّق: تحقّق → حلّ عقدة الدولة (يجب `subscription`) → `ShardManager::activate($node)` → فحص تفرّد البريد على الـ shard → إنشاء المكتب (`status=1` فوري) → `Auth::guard('office')->login()` → توجيه إلى `panel.office.subscription.show`. **بلا مراجعة أدمن.**

## 4) بدء الاشتراك (Stripe Checkout المستضاف)
```
POST /panel/office/subscription/checkout   { plan_key }   → redirect إلى Stripe
```
`SubscriptionBillingService::createCheckoutSession()`: وضع `subscription`، `client_reference_id = office_id`، `subscription_data.trial_period_days`، سعر شهري (`price_data`)، و`metadata { plan_key, office_id, country }` على **الجلسة والاشتراك** معاً (لتوجيه الـ shard في الـ webhooks).

## 5) محرّك الـ Webhook (التفعيل والتجديد التلقائيّان)
```
POST /api/v1/subscriptions/webhook/stripe
```
- تحقّق توقيع Stripe عبر `STRIPE_WEBHOOK_SECRET` (مستقلّ عن webhook الدفعات المفردة).
- يستخرج `country` من الـ metadata → `ShardManager::activate` للـ shard الصحيح قبل المعالجة.
- `SubscriptionWebhookService::apply()` **idempotent** (مفتاح `provider_subscription_id`):

| حدث Stripe | الأثر |
|---|---|
| `checkout.session.completed` | ينشئ اشتراكاً محلياً `trialing` بمعرّفات المزوّد → يبثّ `subscription.activated` |
| `invoice.paid` | `active` + تمديد `current_period_end` |
| `invoice.payment_failed` | `past_due` + يبثّ `subscription.past_due` (تنبيه) |
| `customer.subscription.updated` | مزامنة الحالة/الفترة/الإلغاء |
| `customer.subscription.deleted` | `canceled` |

الأحداث تُبثّ على قناة `office.{id}` **فقط عند تغيّر الحالة**.

## 6) واجهة المكتب
`panel.office.subscription.show` واعية بالمنطقة: لافتة عدّاد التجربة / past_due / التجديد، شبكة خطط بأزرار اشتراك/ترقية (وضع الاشتراك)، أو «لا حاجة لاشتراك» (وضع العمولة).

## 7) قائمة التشغيل الحيّ
1. صفحة «وضع فوترة الدول» → بدّل الدولة إلى **subscription**.
2. `php artisan fleet:shard-provision --all` (3 migrations: billing_mode · trial_days · دورة حياة office_subscriptions).
3. سجّل endpoint الـ webhook في Stripe: `POST /api/v1/subscriptions/webhook/stripe` (الأحداث الخمسة أعلاه).
4. اضبط `STRIPE_SECRET` و `STRIPE_WEBHOOK_SECRET`.

> **عالميّ:** `subscription_plans` (+ `trial_days`) · `infrastructure_nodes.billing_mode`. **مُشارد:** `office_subscriptions` (أعمدة الحياة) · `commission_snapshots`.
